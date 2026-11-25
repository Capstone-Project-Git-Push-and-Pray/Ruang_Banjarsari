<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiModerasi;
use App\Enums\AlasanModerasi;
use App\Enums\JenisKonten;
use App\Http\Controllers\Controller;
use App\Http\Requests\HapusKontenRequest;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\RiwayatModerasi;
use App\Services\ModerasiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModerasiController extends Controller
{
    public function __construct(private ModerasiService $moderasiService) {}

    /**
     * Tampilkan catatan keputusan moderasi konten, dipisah per jenis konten dan dapat difilter (PBI #44, PBI #45).
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'jenis' => ['nullable', Rule::enum(JenisKonten::class)],
            'q' => ['nullable', 'string', 'max:100'],
            'aksi' => ['nullable', Rule::enum(AksiModerasi::class)],
            'sanksi' => ['nullable', Rule::in(['ya', 'tidak'])],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Tanggal awal tidak valid.',
            'tanggal_selesai.date' => 'Tanggal akhir tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $jenisAktif = JenisKonten::tryFrom((string) ($filter['jenis'] ?? ''));
        $kataKunci = trim($filter['q'] ?? '');

        $riwayatTersaring = RiwayatModerasi::query()
            ->when($kataKunci !== '', fn (Builder $query) => $query->whereHas(
                'pemilik',
                fn (Builder $pemilik) => $pemilik->whereAny(['nama_lengkap', 'nik'], 'like', "%{$kataKunci}%")
            ))
            ->when($filter['aksi'] ?? null, fn (Builder $query, string $aksi) => $query->where('aksi', $aksi))
            ->when(($filter['sanksi'] ?? null) === 'ya', fn (Builder $query) => $query->whereHas('sanksi'))
            ->when(($filter['sanksi'] ?? null) === 'tidak', fn (Builder $query) => $query->whereDoesntHave('sanksi'))
            ->when($filter['tanggal_mulai'] ?? null, fn (Builder $query, string $tanggal) => $query->whereDate('created_at', '>=', $tanggal))
            ->when($filter['tanggal_selesai'] ?? null, fn (Builder $query, string $tanggal) => $query->whereDate('created_at', '<=', $tanggal));

        $riwayatModerasi = (clone $riwayatTersaring)
            ->with(['pengaduan', 'pemilik', 'dilakukanOleh', 'sanksi'])
            ->when($jenisAktif, fn (Builder $query) => $query->where('jenis_konten', $jenisAktif))
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        /** @var array<string, int> $jumlahPerJenis */
        $jumlahPerJenis = (clone $riwayatTersaring)->toBase()
            ->selectRaw('jenis_konten, COUNT(*) as jumlah')
            ->groupBy('jenis_konten')
            ->pluck('jumlah', 'jenis_konten')
            ->map(fn ($jumlah) => (int) $jumlah)
            ->all();

        $adaFilter = $kataKunci !== '' || collect($filter)->except(['jenis', 'q'])->filter()->isNotEmpty();

        return view('admin.moderasi.index', compact('riwayatModerasi', 'jenisAktif', 'jumlahPerJenis', 'adaFilter'));
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
