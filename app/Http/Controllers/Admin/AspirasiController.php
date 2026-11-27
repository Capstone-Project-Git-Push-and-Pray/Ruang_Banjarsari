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
use Database\Seeders\AspirasiSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AspirasiController extends Controller
{
    /**
     * Tampilkan daftar aspirasi dan usulan warga desa dengan filter, pencarian, dan paginasi.
     */
    public function index(Request $request, PengingatTindakLanjutService $pengingatService): View
    {
        // Berikan contoh aspirasi otomatis pada mode development jika belum ada data aspirasi
        if (app()->environment('local') && ! app()->runningUnitTests()) {
            if (Pengaduan::withTrashed()->where('jenis', JenisLaporan::Aspirasi)->count() === 0) {
                (new AspirasiSeeder)->run();
            }
        }

        $query = Pengaduan::with(['user', 'kategori', 'lampiran'])
            ->withCount(['dukungan', 'komentar'])
            ->where('jenis', JenisLaporan::Aspirasi)
            ->when($request->query('urut') === 'dukungan', fn ($q) => $q->orderByDesc('dukungan_count'))
            ->latest();

        // Filter berdasarkan visibilitas publik (PBI #44)
        if ($request->query('visibilitas') === 'tampil') {
            $query->tampil();
        } elseif ($request->query('visibilitas') === 'disembunyikan') {
            $query->whereNotNull('disembunyikan_pada');
        }

        // Filter berdasarkan Status Aspirasi
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter berdasarkan Kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->query('kategori_id'));
        }

        // Pencarian teks (judul, deskripsi, lokasi, nama pengusul, NIK)
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

        $aspirasis = $query->paginate(15)->withQueryString();
        $kategoriList = Kategori::where('aktif', true)->orderBy('nama')->get();
        $statusList = StatusLaporan::aspirasiCases();

        // Pengingat aktif dari Kepala Desa untuk aspirasi (PBI #52)
        $kotakPengingat = $pengingatService->aktifUntukJenis(JenisLaporan::Aspirasi);

        return view('admin.aspirasi.index', compact(
            'aspirasis',
            'kategoriList',
            'statusList',
            'kotakPengingat'
        ));
    }

    /**
     * Tampilkan detail aspirasi warga beserta data pengusul, lampiran pendukung, dan riwayat status.
     * Jika aspirasi masih berstatus Diajukan, otomatis ubah menjadi Ditinjau saat admin membuka halaman ini.
     */
    public function show(Request $request, Pengaduan $pengaduan): View
    {
        abort_unless($pengaduan->jenis === JenisLaporan::Aspirasi, 404);
        Gate::authorize('view', $pengaduan);

        // Jika status masih Diajukan, otomatis ubah menjadi Ditinjau saat admin membuka halaman rincian/tindak lanjut
        if ($pengaduan->status === StatusLaporan::Diajukan) {
            DB::transaction(function () use ($pengaduan, $request) {
                $pengaduan->update([
                    'status' => StatusLaporan::Ditinjau,
                ]);

                RiwayatStatus::create([
                    'pengaduan_id' => $pengaduan->id,
                    'status' => StatusLaporan::Ditinjau->value,
                    'catatan' => 'Aspirasi dibuka dan ditinjau oleh Admin Desa.',
                    'diubah_oleh' => $request->user()->id,
                ]);
            });

            // Kirim notifikasi email kepada pelapor jika email terdaftar
            $pelapor = $pengaduan->user;
            if ($pelapor && ! empty($pelapor->email)) {
                try {
                    Mail::to($pelapor->email)->send(new StatusLaporanDiperbaruiMail(
                        $pengaduan,
                        StatusLaporan::Ditinjau,
                        'Aspirasi telah dibuka dan ditinjau oleh Admin Desa.'
                    ));
                } catch (\Throwable $e) {
                    Log::warning("Gagal mengirim notifikasi email otomatis peninjauan aspirasi #{$pengaduan->id}: {$e->getMessage()}");
                }
            }

            $pengaduan->refresh();
        }

        return view('admin.aspirasi.show', [
            'pengaduan' => $pengaduan->load([
                'user',
                'kategori',
                'lampiran',
                'riwayatStatus.diubahOleh',
                'komentar.user',
                'disembunyikanOleh',
            ])->loadCount('dukungan'),
        ]);
    }
}
