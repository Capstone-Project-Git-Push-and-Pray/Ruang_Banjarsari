<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisLaporan;
use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Mail\StatusLaporanDiperbaruiMail;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Services\PengingatTindakLanjutService;
use Database\Seeders\PengaduanSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengaduanController extends Controller
{
    /**
     * Tampilkan seluruh daftar laporan warga desa dengan filter, pencarian, dan paginasi (PBI #27).
     */
    public function index(Request $request, PengingatTindakLanjutService $pengingatService): View
    {
        // Berikan contoh pengaduan otomatis pada mode development jika belum ada data pengaduan
        if (app()->environment('local') && ! app()->runningUnitTests()) {
            if (Pengaduan::where('jenis', JenisLaporan::Pengaduan)->count() === 0) {
                (new PengaduanSeeder)->run();
            }
        }

        $query = Pengaduan::with(['user', 'kategori', 'lampiran'])->latest();

        // Filter berdasarkan Status Laporan
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter berdasarkan Kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->query('kategori_id'));
        }

        // Filter berdasarkan Jenis Laporan (Default adalah Pengaduan saja untuk halaman Kelola Pengaduan)
        $currentJenis = $request->query('jenis', JenisLaporan::Pengaduan->value);
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->query('jenis'));
        } else {
            $query->where('jenis', JenisLaporan::Pengaduan);
        }

        // Pencarian teks (judul, deskripsi, lokasi, nama pelapor, NIK)
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($sub) use ($search) {
                $sub->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        $pengaduans = $query->paginate(15)->withQueryString();

        $kategoriList = Kategori::orderBy('nama')->get();
        $statusList = $currentJenis === JenisLaporan::Aspirasi->value
            ? StatusLaporan::aspirasiCases()
            : StatusLaporan::pengaduanCases();
        $jenisList = JenisLaporan::cases();

        // Pengingat aktif dari Kepala Desa untuk jenis laporan yang sedang ditampilkan (PBI #52)
        $kotakPengingat = $pengingatService->aktifUntukJenis(JenisLaporan::tryFrom((string) $currentJenis) ?? JenisLaporan::Pengaduan);

        return view('admin.pengaduan.index', compact(
            'pengaduans',
            'kategoriList',
            'statusList',
            'jenisList',
            'kotakPengingat'
        ));
    }

    /**
     * Tampilkan detail laporan warga beserta data pelapor, lampiran foto, dan riwayat status (PBI #28).
     */
    public function show(Pengaduan $pengaduan): View
    {
        Gate::authorize('view', $pengaduan);

        return view('admin.pengaduan.show', [
            'pengaduan' => $pengaduan->load([
                'user',
                'kategori',
                'lampiran',
                'riwayatStatus.diubahOleh',
                'komentar.user',
            ]),
        ]);
    }

    /**
     * Perbarui status laporan beserta catatan tindak lanjut (PBI #29).
     * Urutan valid: Diajukan -> Ditinjau -> Diproses -> Selesai / Ditolak.
     * Catatan wajib diisi saat status Ditolak.
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StatusLaporan::class)],
            'catatan' => [
                in_array($request->input('status'), [StatusLaporan::Ditolak->value, StatusLaporan::TidakDapatDitindaklanjuti->value], true) ? 'required' : 'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'status.required' => 'Status baru laporan wajib dipilih.',
            'catatan.required' => 'Catatan penjelasan wajib dicantumkan saat menolak laporan atau tidak dapat menindaklanjuti aspirasi.',
            'catatan.max' => 'Catatan maksimal terdiri dari 1000 karakter.',
        ]);

        $targetStatus = StatusLaporan::from($validated['status']);

        // Jika aspirasi dan targetStatus adalah Ditolak, sinkronkan ke TidakDapatDitindaklanjuti
        if ($pengaduan->isAspirasi() && $targetStatus === StatusLaporan::Ditolak) {
            $targetStatus = StatusLaporan::TidakDapatDitindaklanjuti;
        }

        // Validasi urutan status
        if (! $pengaduan->canTransitionTo($targetStatus)) {
            return back()->withErrors([
                'status' => "Perubahan status dari {$pengaduan->status->label()} ke {$targetStatus->label()} tidak diizinkan.",
            ]);
        }

        DB::transaction(function () use ($pengaduan, $targetStatus, $validated, $request) {
            $pengaduan->update([
                'status' => $targetStatus,
            ]);

            RiwayatStatus::create([
                'pengaduan_id' => $pengaduan->id,
                'status' => $targetStatus->value,
                'catatan' => $validated['catatan'] ?? null,
                'diubah_oleh' => $request->user()->id,
            ]);
        });

        // PBI #32: Kirim notifikasi email kepada warga jika warga memiliki email terdaftar
        $pelapor = $pengaduan->user;
        if ($pelapor && ! empty($pelapor->email)) {
            try {
                Mail::to($pelapor->email)->send(new StatusLaporanDiperbaruiMail(
                    $pengaduan,
                    $targetStatus,
                    $validated['catatan'] ?? null
                ));
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim notifikasi email perubahan status laporan #{$pengaduan->id}: {$e->getMessage()}");
            }
        }

        $redirectRoute = $pengaduan->jenis === JenisLaporan::Aspirasi
            ? 'admin.aspirasi.show'
            : 'admin.pengaduan.show';

        return redirect()->route($redirectRoute, $pengaduan)
            ->with('status', 'status-laporan-diperbarui');
    }
}
