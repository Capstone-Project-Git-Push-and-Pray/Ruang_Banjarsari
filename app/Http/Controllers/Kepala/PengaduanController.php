<?php

namespace App\Http\Controllers\Kepala;

use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengaduanController extends Controller
{
    /**
     * Tampilkan monitoring seluruh laporan pengaduan & aspirasi desa bagi Kepala Desa,
     * dipisah per jenis dan dapat difilter (pencarian, status, kategori, dusun, tanggal, tertunda).
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'jenis' => ['nullable', Rule::enum(JenisLaporan::class)],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(StatusLaporan::class)],
            'kategori' => ['nullable', 'integer', 'exists:kategori,id'],
            'dusun' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'tertunda' => ['nullable', Rule::in(['1'])],
        ], [
            'tanggal_mulai.date' => 'Tanggal awal tidak valid.',
            'tanggal_selesai.date' => 'Tanggal akhir tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $jenisAktif = JenisLaporan::tryFrom((string) ($filter['jenis'] ?? ''));
        $statusAktif = StatusLaporan::tryFrom((string) ($filter['status'] ?? ''));

        $pengaduans = $this->kueriTersaring($filter)
            ->with(['user', 'kategori', 'pengingatTerakhir'])
            ->when($jenisAktif, fn (Builder $query) => $query->where('jenis', $jenisAktif))
            ->when($statusAktif, fn (Builder $query) => $query->where('status', $statusAktif))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Jumlah per tab: status hanya ikut dihitung pada jenis yang memiliki status tersebut
        $jumlahPerJenis = collect(JenisLaporan::cases())
            ->mapWithKeys(fn (JenisLaporan $jenis): array => [
                $jenis->value => $this->kueriTersaring($filter)
                    ->where('jenis', $jenis)
                    ->when(
                        $statusAktif && in_array($statusAktif, $this->statusUntuk($jenis), true),
                        fn (Builder $query) => $query->where('status', $statusAktif),
                    )
                    ->count(),
            ])
            ->all();

        $jumlahTertunda = $this->kueriTersaring(collect($filter)->except('tertunda')->all())
            ->when($jenisAktif, fn (Builder $query) => $query->where('jenis', $jenisAktif))
            ->tap(fn (Builder $query) => $this->hanyaTertunda($query))
            ->count();

        return view('kepala.pengaduan.index', [
            'pengaduans' => $pengaduans,
            'jenisAktif' => $jenisAktif,
            'jumlahPerJenis' => $jumlahPerJenis,
            'jumlahTertunda' => $jumlahTertunda,
            'batasTertundaHari' => Pengaduan::BATAS_TERTUNDA_HARI,
            'pilihanStatus' => $this->statusUntuk($jenisAktif),
            'daftarKategori' => Kategori::query()->orderBy('nama')->pluck('nama', 'id'),
            'daftarDusun' => User::query()
                ->where('role', Role::Warga)
                ->whereNotNull('dusun')
                ->whereHas('pengaduan')
                ->distinct()
                ->orderBy('dusun')
                ->pluck('dusun'),
            'adaFilter' => collect($filter)->except('jenis')->filter()->isNotEmpty(),
        ]);
    }

    /**
     * Tampilkan detail laporan pengaduan untuk supervisi Kepala Desa.
     * Kriteria PBI #22: Data pelapor dapat dilihat oleh Kepala Desa.
     */
    public function show(Pengaduan $pengaduan): View
    {
        Gate::authorize('view', $pengaduan);

        return view('kepala.pengaduan.show', [
            'pengaduan' => $pengaduan->load([
                'user',
                'kategori',
                'lampiran',
                'riwayatStatus.diubahOleh',
                'komentar.user',
            ]),
        ]);
    }

    /**
     * Kueri laporan dengan filter yang berlaku untuk semua tab (selain jenis dan status).
     *
     * @param  array<string, mixed>  $filter
     * @return Builder<Pengaduan>
     */
    private function kueriTersaring(array $filter): Builder
    {
        $kataKunci = trim((string) ($filter['q'] ?? ''));

        return Pengaduan::query()
            ->when($kataKunci !== '', fn (Builder $query) => $query->where(
                fn (Builder $cari) => $cari->where('judul', 'like', "%{$kataKunci}%")
                    ->orWhereHas('user', fn (Builder $pelapor) => $pelapor->whereAny(['nama_lengkap', 'nik'], 'like', "%{$kataKunci}%"))
            ))
            ->when($filter['kategori'] ?? null, fn (Builder $query, $kategori) => $query->where('kategori_id', $kategori))
            ->when($filter['dusun'] ?? null, fn (Builder $query, string $dusun) => $query->whereHas('user', fn (Builder $pelapor) => $pelapor->where('dusun', $dusun)))
            ->when($filter['tanggal_mulai'] ?? null, fn (Builder $query, string $tanggal) => $query->whereDate('created_at', '>=', $tanggal))
            ->when($filter['tanggal_selesai'] ?? null, fn (Builder $query, string $tanggal) => $query->whereDate('created_at', '<=', $tanggal))
            ->when($filter['tertunda'] ?? null, fn (Builder $query) => $this->hanyaTertunda($query));
    }

    /**
     * Batasi ke laporan yang masih Diajukan melewati batas hari tanpa tanggapan petugas.
     *
     * @param  Builder<Pengaduan>  $query
     */
    private function hanyaTertunda(Builder $query): void
    {
        $query->perluTindakLanjut();
    }

    /**
     * Status yang relevan untuk jenis laporan (gabungan keduanya bila jenis tidak dipilih).
     *
     * @return list<StatusLaporan>
     */
    private function statusUntuk(?JenisLaporan $jenis): array
    {
        return match ($jenis) {
            JenisLaporan::Pengaduan => StatusLaporan::pengaduanCases(),
            JenisLaporan::Aspirasi => StatusLaporan::aspirasiCases(),
            null => collect([...StatusLaporan::pengaduanCases(), ...StatusLaporan::aspirasiCases()])
                ->unique(fn (StatusLaporan $status): string => $status->value)
                ->values()
                ->all(),
        };
    }
}
