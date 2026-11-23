<?php

namespace App\Http\Controllers\Warga;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengaduanRequest;
use App\Models\Kategori;
use App\Models\Lampiran;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PengaduanController extends Controller
{
    /**
     * Tampilkan riwayat laporan pengaduan & aspirasi milik warga yang sedang login (PBI #26).
     * Kriteria: Warga hanya melihat daftar laporan miliknya sendiri, terurut terbaru, status tampil di daftar.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $baseQuery = Pengaduan::where('user_id', $user->id);

        $totalCount = (clone $baseQuery)->count();

        $sedangBerjalanCount = (clone $baseQuery)->whereIn('status', [
            StatusLaporan::Diajukan,
            StatusLaporan::Ditinjau,
            StatusLaporan::Diproses,
            StatusLaporan::Ditampung,
            StatusLaporan::DalamPembahasan,
        ])->count();

        $selesaiCount = (clone $baseQuery)->whereIn('status', [
            StatusLaporan::Selesai,
            StatusLaporan::Direalisasikan,
        ])->count();

        $tidakDilanjutkanCount = (clone $baseQuery)->whereIn('status', [
            StatusLaporan::Ditolak,
            StatusLaporan::TidakDapatDitindaklanjuti,
        ])->count();

        $pengaduans = (clone $baseQuery)
            ->with(['kategori', 'lampiran', 'komentar' => fn ($query) => $query->tampil()])
            ->withCount(['dukungan', 'komentar as komentar_masuk_count' => fn ($query) => $query->komentarMasuk()])
            ->latest()
            ->paginate(50);

        return view('warga.pengaduan.index', compact(
            'pengaduans',
            'totalCount',
            'sedangBerjalanCount',
            'selesaiCount',
            'tidakDilanjutkanCount'
        ));
    }

    /**
     * Formulir pembuatan pengaduan baru (PBI #24).
     */
    public function create(): View
    {
        $kategoriList = Kategori::where('aktif', true)->orderBy('nama')->get();

        return view('warga.pengaduan.create', compact('kategoriList'));
    }

    /**
     * Simpan pengaduan baru beserta foto pendukung (PBI #24 & PBI #25).
     * Kriteria:
     * - Hanya akun Aktif (divalidasi oleh middleware akun.aktif dan StorePengaduanRequest)
     * - Laporan tersimpan berstatus Diajukan beserta identitas pelapor dan waktu otomatis
     * - Tidak ada laporan anonim (user_id diisi dari pengguna yang login)
     * - Lampiran foto maks 3 file JPG/PNG maks 2 MB tersimpan di storage
     */
    public function store(StorePengaduanRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $pengaduan = DB::transaction(function () use ($validated, $user, $request) {
            $pengaduan = Pengaduan::create([
                'user_id' => $user->id,
                'jenis' => $validated['jenis'],
                'kategori_id' => $validated['kategori_id'],
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'lokasi' => $validated['lokasi'] ?? null,
                'status' => StatusLaporan::Diajukan,
            ]);

            // PBI #25: Simpan foto lampiran jika ada
            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $file) {
                    $path = $file->store(Lampiran::FOLDER, Lampiran::DISK);

                    Lampiran::create([
                        'pengaduan_id' => $pengaduan->id,
                        'path' => $path,
                        'nama_asli' => $file->getClientOriginalName(),
                    ]);
                }
            }

            // Catat riwayat status awal 'diajukan'
            RiwayatStatus::create([
                'pengaduan_id' => $pengaduan->id,
                'status' => StatusLaporan::Diajukan->value,
                'catatan' => 'Laporan diajukan oleh warga.',
                'diubah_oleh' => $user->id,
            ]);

            return $pengaduan;
        });

        return redirect()->route('warga.pengaduan.show', $pengaduan)
            ->with('status', 'laporan-berhasil-diajukan');
    }

    /**
     * Tampilkan detail laporan pengaduan milik warga (PBI #26).
     * Kriteria: Warga tidak bisa membuka laporan warga lain lewat URL (menghasilkan 403).
     */
    public function show(Request $request, Pengaduan $pengaduan): View
    {
        Gate::authorize('view', $pengaduan);

        return view('warga.pengaduan.show', [
            'pengaduan' => $pengaduan->load([
                'kategori',
                'user',
                'lampiran',
                'riwayatStatus.diubahOleh',
                'komentar' => fn ($query) => $query->tampilUntuk($request->user())->with('user'),
            ])->loadCount(['dukungan', 'komentar as komentar_masuk_count' => fn ($query) => $query->komentarMasuk()]),
        ]);
    }
}
