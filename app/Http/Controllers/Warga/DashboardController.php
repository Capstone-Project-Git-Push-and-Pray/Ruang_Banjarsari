<?php

namespace App\Http\Controllers\Warga;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Halaman Utama Dashboard Warga.
     */
    public function index(): View
    {
        $userId = Auth::id();

        $totalLaporan = Pengaduan::where('user_id', $userId)->count();

        $countDiajukan = Pengaduan::where('user_id', $userId)
            ->where('status', StatusLaporan::Diajukan)
            ->count();

        $countDitinjau = Pengaduan::where('user_id', $userId)
            ->where('status', StatusLaporan::Ditinjau)
            ->count();

        $countDiproses = Pengaduan::where('user_id', $userId)
            ->whereIn('status', [
                StatusLaporan::Diproses,
                StatusLaporan::Ditampung,
                StatusLaporan::DalamPembahasan,
            ])
            ->count();

        $countSelesai = Pengaduan::where('user_id', $userId)
            ->whereIn('status', [
                StatusLaporan::Selesai,
                StatusLaporan::Direalisasikan,
            ])
            ->count();

        $countDitolak = Pengaduan::where('user_id', $userId)
            ->whereIn('status', [
                StatusLaporan::Ditolak,
                StatusLaporan::TidakDapatDitindaklanjuti,
            ])
            ->count();

        $laporanTerbaru = Pengaduan::where('user_id', $userId)
            ->with(['kategori'])
            ->withCount(['komentar' => fn ($query) => $query->tampil()])
            ->latest()
            ->take(5)
            ->get();

        return view('warga.dashboard', [
            'totalLaporan' => $totalLaporan,
            'countDiajukan' => $countDiajukan,
            'countDitinjau' => $countDitinjau,
            'countDiproses' => $countDiproses,
            'countSelesai' => $countSelesai,
            'countDitolak' => $countDitolak,
            'laporanTerbaru' => $laporanTerbaru,
        ]);
    }
}
