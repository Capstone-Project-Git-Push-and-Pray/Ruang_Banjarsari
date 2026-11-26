<?php

namespace App\Services;

use App\Enums\AksiModerasi;
use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Models\RiwayatModerasi;
use App\Models\RiwayatStatus;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Menyusun log aktivitas petugas (Admin Desa dan Kepala Desa) dari data asli:
 * perubahan status laporan, peninjauan pendaftaran warga, moderasi konten, dan pencabutan sanksi.
 *
 * Keempat sumber digabung dengan UNION agar urutan, filter, dan penomoran halaman tetap akurat.
 */
class AktivitasPetugasService
{
    public const JENIS_STATUS = 'status';

    public const JENIS_PENDAFTARAN = 'pendaftaran';

    public const JENIS_MODERASI = 'moderasi';

    public const JENIS_SANKSI = 'sanksi';

    /**
     * Label tampilan tiap jenis aktivitas, sesuai urutan tab.
     *
     * @return array<string, string>
     */
    public static function daftarJenis(): array
    {
        return [
            self::JENIS_STATUS => 'Status Laporan',
            self::JENIS_PENDAFTARAN => 'Pendaftaran',
            self::JENIS_MODERASI => 'Moderasi',
            self::JENIS_SANKSI => 'Sanksi',
        ];
    }

    /**
     * Aktivitas terbaru untuk kartu di dashboard.
     *
     * @return Collection<int, array{jenis: string, judul: string, deskripsi: string, petugas: string, waktu: Carbon, warna: string, cincin: string}>
     */
    public function terbaru(int $jumlah = 6): Collection
    {
        return $this->lengkapi($this->kueri()->limit($jumlah)->get());
    }

    /**
     * Seluruh aktivitas yang sesuai filter, per halaman.
     *
     * @param  array{jenis?: ?string, petugas?: ?string, tanggal_mulai?: ?string, tanggal_selesai?: ?string}  $filter
     */
    public function daftar(array $filter, int $perHalaman = 20): LengthAwarePaginator
    {
        $halaman = $this->kueri($filter)->paginate($perHalaman)->withQueryString();

        return $halaman->setCollection($this->lengkapi(collect($halaman->items())));
    }

    /**
     * Jumlah aktivitas per jenis untuk lencana tab (mengikuti filter selain jenis).
     *
     * @param  array{petugas?: ?string, tanggal_mulai?: ?string, tanggal_selesai?: ?string}  $filter
     * @return array<string, int>
     */
    public function jumlahPerJenis(array $filter): array
    {
        return DB::query()
            ->fromSub($this->gabungan(), 'aktivitas')
            ->tap(fn (Builder $query) => $this->terapkanFilter($query, ['jenis' => null] + $filter))
            ->selectRaw('jenis, COUNT(*) as jumlah')
            ->groupBy('jenis')
            ->pluck('jumlah', 'jenis')
            ->map(fn ($jumlah): int => (int) $jumlah)
            ->all();
    }

    /**
     * Kueri gabungan yang sudah difilter dan diurutkan dari yang terbaru.
     *
     * @param  array{jenis?: ?string, petugas?: ?string, tanggal_mulai?: ?string, tanggal_selesai?: ?string}  $filter
     */
    private function kueri(array $filter = []): Builder
    {
        return DB::query()
            ->fromSub($this->gabungan(), 'aktivitas')
            ->tap(fn (Builder $query) => $this->terapkanFilter($query, $filter))
            ->orderByDesc('waktu')
            ->orderByDesc('sumber_id');
    }

    /**
     * @param  array{jenis?: ?string, petugas?: ?string, tanggal_mulai?: ?string, tanggal_selesai?: ?string}  $filter
     */
    private function terapkanFilter(Builder $query, array $filter): void
    {
        $query
            ->when($filter['jenis'] ?? null, fn (Builder $query, string $jenis) => $query->where('jenis', $jenis))
            ->when($filter['petugas'] ?? null, fn (Builder $query, string $petugas) => $query->where('petugas_id', $petugas))
            ->when($filter['tanggal_mulai'] ?? null, fn (Builder $query, string $tanggal) => $query->whereDate('waktu', '>=', $tanggal))
            ->when($filter['tanggal_selesai'] ?? null, fn (Builder $query, string $tanggal) => $query->whereDate('waktu', '<=', $tanggal));
    }

    /**
     * UNION keempat sumber dengan kolom seragam: jenis, sumber_id, petugas_id, waktu.
     */
    private function gabungan(): Builder
    {
        $perubahanStatus = DB::table('riwayat_status')
            ->join('users as petugas', 'petugas.id', '=', 'riwayat_status.diubah_oleh')
            ->where('petugas.role', '!=', Role::Warga->value)
            ->selectRaw("'".self::JENIS_STATUS."' as jenis, riwayat_status.id as sumber_id, riwayat_status.diubah_oleh as petugas_id, riwayat_status.created_at as waktu");

        $peninjauanPendaftaran = DB::table('users')
            ->where('role', Role::Warga->value)
            ->whereNotNull('ditinjau_pada')
            ->whereIn('status_akun', [StatusAkun::Aktif->value, StatusAkun::Ditolak->value])
            ->selectRaw("'".self::JENIS_PENDAFTARAN."' as jenis, id as sumber_id, ditinjau_oleh as petugas_id, ditinjau_pada as waktu");

        $moderasiKonten = DB::table('riwayat_moderasi')
            ->selectRaw("'".self::JENIS_MODERASI."' as jenis, id as sumber_id, dilakukan_oleh as petugas_id, created_at as waktu");

        $pencabutanSanksi = DB::table('sanksi')
            ->whereNotNull('dicabut_pada')
            ->selectRaw("'".self::JENIS_SANKSI."' as jenis, id as sumber_id, diubah_oleh as petugas_id, dicabut_pada as waktu");

        return $perubahanStatus
            ->unionAll($peninjauanPendaftaran)
            ->unionAll($moderasiKonten)
            ->unionAll($pencabutanSanksi);
    }

    /**
     * Muat model sumber tiap baris (eager load per jenis) lalu ubah menjadi butir log, urutan tetap.
     *
     * @param  Collection<int, object{jenis: string, sumber_id: int}>  $baris
     * @return Collection<int, array{jenis: string, judul: string, deskripsi: string, petugas: string, waktu: Carbon, warna: string, cincin: string}>
     */
    private function lengkapi(Collection $baris): Collection
    {
        $idPerJenis = $baris->groupBy('jenis')->map(fn (Collection $kelompok) => $kelompok->pluck('sumber_id')->all());

        $model = [
            self::JENIS_STATUS => RiwayatStatus::query()
                ->with(['diubahOleh', 'pengaduan' => fn ($query) => $query->withTrashed()])
                ->findMany($idPerJenis->get(self::JENIS_STATUS, []))->keyBy('id'),
            self::JENIS_PENDAFTARAN => User::query()->with('peninjau')
                ->findMany($idPerJenis->get(self::JENIS_PENDAFTARAN, []))->keyBy('id'),
            self::JENIS_MODERASI => RiwayatModerasi::query()->with(['dilakukanOleh', 'pemilik'])
                ->findMany($idPerJenis->get(self::JENIS_MODERASI, []))->keyBy('id'),
            self::JENIS_SANKSI => Sanksi::query()->with(['diubahOleh', 'user'])
                ->findMany($idPerJenis->get(self::JENIS_SANKSI, []))->keyBy('id'),
        ];

        return $baris
            ->map(function (object $aktivitas) use ($model): ?array {
                $sumber = $model[$aktivitas->jenis]->get($aktivitas->sumber_id);

                return match (true) {
                    $sumber instanceof RiwayatStatus => $this->perubahanStatus($sumber),
                    $sumber instanceof User => $this->peninjauanPendaftaran($sumber),
                    $sumber instanceof RiwayatModerasi => $this->moderasiKonten($sumber),
                    $sumber instanceof Sanksi => $this->pencabutanSanksi($sumber),
                    default => null,
                };
            })
            ->filter()
            ->values();
    }

    /**
     * @return array{jenis: string, judul: string, deskripsi: string, petugas: string, waktu: Carbon, warna: string, cincin: string}
     */
    private function perubahanStatus(RiwayatStatus $riwayat): array
    {
        $laporan = $riwayat->pengaduan;
        $namaLaporan = $laporan === null
            ? 'laporan yang telah dihapus'
            : Str::lower($laporan->jenis->label()).' "'.Str::limit($laporan->judul, 45).'"';
        $petugas = $this->namaPetugas($riwayat->diubahOleh);

        return [
            'jenis' => self::JENIS_STATUS,
            'judul' => 'Status Laporan Diperbarui',
            'deskripsi' => $petugas.' mengubah status '.$namaLaporan.' menjadi '.$riwayat->status->label().'.',
            'petugas' => $petugas,
            'waktu' => $riwayat->created_at,
            'warna' => 'bg-[#1F6B4F]',
            'cincin' => 'ring-emerald-50',
        ];
    }

    /**
     * @return array{jenis: string, judul: string, deskripsi: string, petugas: string, waktu: Carbon, warna: string, cincin: string}
     */
    private function peninjauanPendaftaran(User $warga): array
    {
        $disetujui = $warga->status_akun === StatusAkun::Aktif;
        $petugas = $this->namaPetugas($warga->peninjau);

        return [
            'jenis' => self::JENIS_PENDAFTARAN,
            'judul' => $disetujui ? 'Pendaftaran Warga Disetujui' : 'Pendaftaran Warga Ditolak',
            'deskripsi' => $petugas.($disetujui ? ' menyetujui' : ' menolak').' pendaftaran akun '.$warga->nama_lengkap.'.',
            'petugas' => $petugas,
            'waktu' => Carbon::parse($warga->ditinjau_pada),
            'warna' => $disetujui ? 'bg-blue-600' : 'bg-rose-500',
            'cincin' => $disetujui ? 'ring-blue-50' : 'ring-rose-50',
        ];
    }

    /**
     * @return array{jenis: string, judul: string, deskripsi: string, petugas: string, waktu: Carbon, warna: string, cincin: string}
     */
    private function moderasiKonten(RiwayatModerasi $riwayat): array
    {
        $kataKerja = match ($riwayat->aksi) {
            AksiModerasi::Sembunyikan => 'menyembunyikan',
            AksiModerasi::Tampilkan => 'menampilkan kembali',
            AksiModerasi::Hapus => 'menghapus',
        };
        $pemilik = $riwayat->pemilik?->nama_lengkap;
        $petugas = $this->namaPetugas($riwayat->dilakukanOleh);

        return [
            'jenis' => self::JENIS_MODERASI,
            'judul' => 'Konten '.$riwayat->aksi->label(),
            'deskripsi' => $petugas.' '.$kataKerja.' '.Str::lower($riwayat->jenis_konten->label())
                .($pemilik ? ' milik '.$pemilik : '')
                .($riwayat->alasan ? ' ('.Str::lower($riwayat->alasan->label()).')' : '').'.',
            'petugas' => $petugas,
            'waktu' => $riwayat->created_at,
            'warna' => match ($riwayat->aksi) {
                AksiModerasi::Sembunyikan => 'bg-amber-500',
                AksiModerasi::Tampilkan => 'bg-teal-600',
                AksiModerasi::Hapus => 'bg-red-600',
            },
            'cincin' => match ($riwayat->aksi) {
                AksiModerasi::Sembunyikan => 'ring-amber-50',
                AksiModerasi::Tampilkan => 'ring-teal-50',
                AksiModerasi::Hapus => 'ring-red-50',
            },
        ];
    }

    /**
     * @return array{jenis: string, judul: string, deskripsi: string, petugas: string, waktu: Carbon, warna: string, cincin: string}
     */
    private function pencabutanSanksi(Sanksi $sanksi): array
    {
        $dibatalkan = $sanksi->jenis_pencabutan === Sanksi::PENCABUTAN_DIBATALKAN;
        $petugas = $this->namaPetugas($sanksi->diubahOleh);

        return [
            'jenis' => self::JENIS_SANKSI,
            'judul' => $dibatalkan ? 'Sanksi Dibatalkan' : 'Sanksi Diakhiri',
            'deskripsi' => $petugas.($dibatalkan ? ' membatalkan' : ' mengakhiri')
                .' sanksi '.Str::lower($sanksi->fitur->label())
                .' milik '.($sanksi->user?->nama_lengkap ?? 'warga').'.',
            'petugas' => $petugas,
            'waktu' => $sanksi->dicabut_pada,
            'warna' => 'bg-purple-600',
            'cincin' => 'ring-purple-50',
        ];
    }

    /**
     * Nama petugas pelaku aktivitas, dengan cadangan bila akunnya sudah dihapus.
     */
    private function namaPetugas(?User $petugas): string
    {
        return $petugas?->nama_lengkap ?? 'Petugas';
    }
}
