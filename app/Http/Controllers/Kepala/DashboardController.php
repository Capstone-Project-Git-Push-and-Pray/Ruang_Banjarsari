<?php

namespace App\Http\Controllers\Kepala;

use App\Enums\Role;
use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard Pemantauan Kepala Desa (PBI #36).
     * Kriteria:
     * - Menampilkan rekapitulasi laporan desa.
     * - Menampilkan daftar laporan terbaru untuk pemantauan tindak lanjut.
     * - Bersifat read-only tanpa tombol ubah status atau hapus.
     */
    public function index(): View
    {
        $totalLaporan = Pengaduan::count();
        $totalWarga = User::where('role', Role::Warga)->count();

        // Rekapitulasi Status Laporan
        $countDiajukan = Pengaduan::where('status', StatusLaporan::Diajukan)->count();
        $countDitinjau = Pengaduan::where('status', StatusLaporan::Ditinjau)->count();
        $countDiproses = Pengaduan::where('status', StatusLaporan::Diproses)->count();
        $countSelesai = Pengaduan::where('status', StatusLaporan::Selesai)->count();
        $countDitolak = Pengaduan::where('status', StatusLaporan::Ditolak)->count();

        $tingkatPenyelesaian = $totalLaporan > 0
            ? round(($countSelesai / $totalLaporan) * 100, 1)
            : 0;

        // Distribusi Kategori
        $kategoriBreakdown = Kategori::withCount('pengaduan')
            ->orderByDesc('pengaduan_count')
            ->take(5)
            ->get();

        // 5 Laporan Terbaru untuk Pemantauan Pimpinan (Read-Only)
        $laporanTerbaru = Pengaduan::with(['user', 'kategori'])
            ->latest()
            ->take(5)
            ->get();

        return view('kepala.dashboard', compact(
            'totalLaporan',
            'totalWarga',
            'countDiajukan',
            'countDitinjau',
            'countDiproses',
            'countSelesai',
            'countDitolak',
            'tingkatPenyelesaian',
            'kategoriBreakdown',
            'laporanTerbaru'
        ));
    }
}
