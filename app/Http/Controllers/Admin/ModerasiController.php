<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AlasanModerasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\HapusKontenRequest;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\RiwayatModerasi;
use App\Services\ModerasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ModerasiController extends Controller
{
    public function __construct(private ModerasiService $moderasiService) {}

    /**
     * Tampilkan catatan seluruh keputusan moderasi konten (PBI #44, PBI #45).
     */
    public function index(): View
    {
        $riwayatModerasi = RiwayatModerasi::with(['pengaduan', 'pemilik', 'dilakukanOleh', 'sanksi'])
            ->latest()
            ->latest('id')
            ->paginate(20);

        return view('admin.moderasi.index', compact('riwayatModerasi'));
    }

    /**
     * Sembunyikan aspirasi dari publik (PBI #44).
     */
    public function sembunyikanAspirasi(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        Gate::authorize('moderasi', $pengaduan);
        $this->moderasiService->sembunyikan($pengaduan, $request->user());

        return back()->with('success', 'Aspirasi disembunyikan dan tidak lagi terlihat oleh publik.');
    }

    /**
     * Tampilkan kembali aspirasi yang disembunyikan (PBI #44).
     */
    public function tampilkanAspirasi(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        Gate::authorize('moderasi', $pengaduan);
        $this->moderasiService->tampilkan($pengaduan, $request->user());

        return back()->with('success', 'Aspirasi kembali ditampilkan kepada publik.');
    }

    /**
     * Hapus aspirasi dengan alasan yang tercatat (PBI #45).
     */
    public function hapusAspirasi(HapusKontenRequest $request, Pengaduan $pengaduan): RedirectResponse
    {
        $riwayat = $this->hapus($request, $pengaduan);

        return redirect()->route('admin.aspirasi.index')
            ->with('success', $this->pesanHapus('Aspirasi', $riwayat));
    }

    /**
     * Sembunyikan komentar dari publik (PBI #44).
     */
    public function sembunyikanKomentar(Request $request, Komentar $komentar): RedirectResponse
    {
        Gate::authorize('moderasi', $komentar);
        $this->moderasiService->sembunyikan($komentar, $request->user());

        return back()->with('success', 'Komentar disembunyikan dan tidak lagi terlihat oleh publik.');
    }

    /**
     * Tampilkan kembali komentar yang disembunyikan (PBI #44).
     */
    public function tampilkanKomentar(Request $request, Komentar $komentar): RedirectResponse
    {
        Gate::authorize('moderasi', $komentar);
        $this->moderasiService->tampilkan($komentar, $request->user());

        return back()->with('success', 'Komentar kembali ditampilkan kepada publik.');
    }

    /**
     * Hapus komentar dengan alasan yang tercatat (PBI #45).
     */
    public function hapusKomentar(HapusKontenRequest $request, Komentar $komentar): RedirectResponse
    {
        $riwayat = $this->hapus($request, $komentar);

        return back()->with('success', $this->pesanHapus('Komentar', $riwayat));
    }

    /**
     * Teruskan keputusan penghapusan yang sudah tervalidasi ke layanan moderasi.
     */
    private function hapus(HapusKontenRequest $request, Pengaduan|Komentar $konten): ?RiwayatModerasi
    {
        $validated = $request->validated();

        return $this->moderasiService->hapus(
            $konten,
            $request->user(),
            AlasanModerasi::from($validated['alasan']),
            $validated['catatan_moderasi'] ?? null,
        );
    }

    /**
     * Pesan hasil penghapusan, termasuk sanksi otomatis yang diterapkan (PBI #46, PBI #47).
     */
    private function pesanHapus(string $jenis, ?RiwayatModerasi $riwayat): string
    {
        $pesan = "{$jenis} dihapus dan alasannya telah dicatat.";
        $sanksi = $riwayat?->sanksi;

        if ($sanksi !== null) {
            $pesan .= " Sanksi otomatis tingkat {$sanksi->tingkat} untuk fitur {$sanksi->fitur->label()}: {$sanksi->keteranganMasaBerlaku()}.";
        }

        return $pesan;
    }
}
