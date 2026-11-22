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
        $riwayatModerasi = RiwayatModerasi::with(['pengaduan', 'pemilik', 'dilakukanOleh'])
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
        $this->hapus($request, $pengaduan);

        return redirect()->route('admin.aspirasi.index')
            ->with('success', 'Aspirasi dihapus dan alasannya telah dicatat.');
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
        $this->hapus($request, $komentar);

        return back()->with('success', 'Komentar dihapus dan alasannya telah dicatat.');
    }

    /**
     * Teruskan keputusan penghapusan yang sudah tervalidasi ke layanan moderasi.
     */
    private function hapus(HapusKontenRequest $request, Pengaduan|Komentar $konten): void
    {
        $validated = $request->validated();

        $this->moderasiService->hapus(
            $konten,
            $request->user(),
            AlasanModerasi::from($validated['alasan']),
            $validated['catatan_moderasi'] ?? null,
        );
    }
}
