<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Pusat Komando & Dashboard Pengelolaan Admin Desa.
     */
    public function index(): View
    {
        $totalAduan = Pengaduan::count();

        // Rekapitulasi per Status (mencakup alur Pengaduan dan Aspirasi Warga)
        $countDiajukan = Pengaduan::where('status', StatusLaporan::Diajukan)->count();
        $countDitinjau = Pengaduan::where('status', StatusLaporan::Ditinjau)->count();
        $countDiproses = Pengaduan::whereIn('status', [
            StatusLaporan::Diproses,
            StatusLaporan::Ditampung,
            StatusLaporan::DalamPembahasan,
        ])->count();
        $countSelesai = Pengaduan::whereIn('status', [
            StatusLaporan::Selesai,
            StatusLaporan::Direalisasikan,
        ])->count();
        $countDitolak = Pengaduan::whereIn('status', [
            StatusLaporan::Ditolak,
            StatusLaporan::TidakDapatDitindaklanjuti,
        ])->count();

        $tingkatPenyelesaian = $totalAduan > 0
            ? round(($countSelesai / $totalAduan) * 100, 1)
            : 0;

        // Verifikasi Akun Warga Menunggu Tinjauan
        $pendingWargaQuery = User::where('role', Role::Warga)
            ->where('status_akun', StatusAkun::MenungguTinjauan);

        $pendingWargaCount = $pendingWargaQuery->count();
        $pendingWarga = (clone $pendingWargaQuery)->latest()->take(3)->get();

        // Distribusi Kategori Masalah
        $kategoriBreakdown = Kategori::withCount('pengaduan')
            ->orderByDesc('pengaduan_count')
            ->take(4)
            ->get();

        // Antrean Aduan Butuh Penanganan / Disposisi Cepat
        $antreanAduan = Pengaduan::with(['user', 'kategori'])
            ->latest()
            ->take(6)
            ->get();

        // Log Aktivitas Petugas Terkini
        $logAktivitas = RiwayatStatus::with(['pengaduan', 'diubahOleh'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalAduan',
            'countDiajukan',
            'countDitinjau',
            'countDiproses',
            'countSelesai',
            'countDitolak',
            'tingkatPenyelesaian',
            'pendingWargaCount',
            'pendingWarga',
            'kategoriBreakdown',
            'antreanAduan',
            'logAktivitas',
        ));
    }
}
