<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FiturSanksi;
use App\Http\Controllers\Controller;
use App\Http\Requests\UbahSanksiRequest;
use App\Models\Sanksi;
use App\Services\SanksiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SanksiController extends Controller
{
    public function __construct(private SanksiService $sanksiService) {}

    /**
     * Tampilkan seluruh sanksi warga, dipisah per fitur dan dapat difilter (PBI #49).
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'fitur' => ['nullable', Rule::enum(FiturSanksi::class)],
            'status' => ['nullable', Rule::in(['berlaku', 'semua'])],
        ]);

        $kataKunci = trim($filter['q'] ?? '');
        $fiturAktif = FiturSanksi::tryFrom((string) ($filter['fitur'] ?? ''));
        $statusAktif = $filter['status'] ?? 'berlaku';

        $sanksiTersaring = Sanksi::query()
            ->when($statusAktif === 'berlaku', fn ($query) => $query->berlaku())
            ->when($kataKunci !== '', fn ($query) => $query->whereHas(
                'user',
                fn ($userQuery) => $userQuery->whereAny(['nama_lengkap', 'nik'], 'like', "%{$kataKunci}%")
            ));

        $sanksiList = (clone $sanksiTersaring)
            ->with(['user', 'riwayatModerasi', 'diubahOleh'])
            ->when($fiturAktif, fn ($query) => $query->where('fitur', $fiturAktif))
            ->latest('mulai_pada')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        /** @var array<string, int> $jumlahPerFitur */
        $jumlahPerFitur = (clone $sanksiTersaring)->toBase()
            ->selectRaw('fitur, COUNT(*) as jumlah')
            ->groupBy('fitur')
            ->pluck('jumlah', 'fitur')
            ->map(fn ($jumlah) => (int) $jumlah)
            ->all();

        return view('admin.sanksi.index', [
            'sanksiList' => $sanksiList,
            'jumlahBerlaku' => Sanksi::berlaku()->count(),
            'fiturAktif' => $fiturAktif,
            'jumlahPerFitur' => $jumlahPerFitur,
            'adaFilter' => $kataKunci !== '' || $statusAktif !== 'berlaku',
        ]);
    }

    /**
     * Ubah masa berlaku, buka kembali, atau batalkan sanksi warga (PBI #49).
     */
    public function update(UbahSanksiRequest $request, Sanksi $sanksi): RedirectResponse
    {
        $validated = $request->validated();
        $admin = $request->user();

        if ($sanksi->jenis_pencabutan === Sanksi::PENCABUTAN_DIBATALKAN
            || ($validated['tindakan'] !== Sanksi::PENCABUTAN_DIBATALKAN && $sanksi->dicabut_pada !== null)) {
            return back()->with('error', 'Sanksi ini sudah dicabut sehingga tidak dapat diubah lagi.');
        }

        $pesan = match ($validated['tindakan']) {
            'ubah' => $this->ubahMasaBerlaku($request, $sanksi),
            Sanksi::PENCABUTAN_DIAKHIRI => 'Sanksi diakhiri dan fitur warga dibuka kembali.',
            Sanksi::PENCABUTAN_DIBATALKAN => 'Sanksi dibatalkan dan tidak dihitung sebagai pelanggaran.',
        };

        if ($validated['tindakan'] !== 'ubah') {
            $this->sanksiService->cabut($sanksi, $validated['tindakan'], $admin, $validated['catatan_admin']);
        }

        return back()->with('success', $pesan);
    }

    /**
     * Simpan masa berlaku baru dan kembalikan pesan untuk admin.
     */
    private function ubahMasaBerlaku(UbahSanksiRequest $request, Sanksi $sanksi): string
    {
        $berakhirPada = $request->boolean('permanen') ? null : Carbon::parse($request->validated('berakhir_pada'));

        $this->sanksiService->ubahMasaBerlaku($sanksi, $berakhirPada, $request->user(), $request->validated('catatan_admin'));

        return 'Masa berlaku sanksi diperbarui.';
    }
}
