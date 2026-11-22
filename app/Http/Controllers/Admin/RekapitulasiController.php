<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisLaporan;
use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapitulasiController extends Controller
{
    /**
     * Tampilkan halaman rekapitulasi laporan per status dan kategori (PBI #35).
     * Kriteria:
     * - Jumlah akurat
     * - Filter rentang tanggal (tanggal_mulai, tanggal_selesai)
     * - Grafik dan tabel sinkron
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? null;
        $tanggalSelesai = $validated['tanggal_selesai'] ?? null;

        // Query dasar dengan filter rentang tanggal
        $baseQuery = Pengaduan::query()
            ->when($tanggalMulai, fn ($q) => $q->whereDate('created_at', '>=', $tanggalMulai))
            ->when($tanggalSelesai, fn ($q) => $q->whereDate('created_at', '<=', $tanggalSelesai));

        $totalLaporan = (clone $baseQuery)->count();

        // 1. Rekapitulasi per Status
        $rekapStatus = [];
        foreach (StatusLaporan::cases() as $status) {
            $count = (clone $baseQuery)->where('status', $status)->count();
            $percentage = $totalLaporan > 0 ? round(($count / $totalLaporan) * 100, 1) : 0;
            $rekapStatus[$status->value] = [
                'status' => $status,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // 2. Rekapitulasi per Kategori
        $kategoris = Kategori::all();
        $rekapKategori = $kategoris->map(function ($kategori) use ($baseQuery, $totalLaporan) {
            $count = (clone $baseQuery)->where('kategori_id', $kategori->id)->count();
            $percentage = $totalLaporan > 0 ? round(($count / $totalLaporan) * 100, 1) : 0;

            return [
                'id' => $kategori->id,
                'nama' => $kategori->nama,
                'aktif' => $kategori->aktif,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->sortByDesc('count')->values();

        // 3. Rekapitulasi per Jenis Laporan (Pengaduan vs Aspirasi)
        $rekapJenis = [];
        foreach (JenisLaporan::cases() as $jenis) {
            $count = (clone $baseQuery)->where('jenis', $jenis)->count();
            $percentage = $totalLaporan > 0 ? round(($count / $totalLaporan) * 100, 1) : 0;
            $rekapJenis[$jenis->value] = [
                'jenis' => $jenis,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // Ringkasan metrik eksekutif
        $metrics = [
            'total' => $totalLaporan,
            'menunggu' => ($rekapStatus[StatusLaporan::Diajukan->value]['count'] ?? 0) + ($rekapStatus[StatusLaporan::Ditinjau->value]['count'] ?? 0),
            'diproses' => $rekapStatus[StatusLaporan::Diproses->value]['count'] ?? 0,
            'selesai' => $rekapStatus[StatusLaporan::Selesai->value]['count'] ?? 0,
            'ditolak' => $rekapStatus[StatusLaporan::Ditolak->value]['count'] ?? 0,
        ];

        return view('admin.rekapitulasi.index', compact(
            'metrics',
            'rekapStatus',
            'rekapKategori',
            'rekapJenis',
            'totalLaporan',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }
}
