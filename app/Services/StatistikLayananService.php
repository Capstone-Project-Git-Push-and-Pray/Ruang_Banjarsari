<?php

namespace App\Services;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Statistik kinerja layanan untuk halaman utama, dihitung langsung dari data laporan
 * (pengaduan dan aspirasi) dalam satu tahun terakhir.
 */
class StatistikLayananService
{
    /**
     * @var list<StatusLaporan>
     */
    private const STATUS_SELESAI = [StatusLaporan::Selesai, StatusLaporan::Direalisasikan];

    /**
     * @var list<StatusLaporan>
     */
    private const STATUS_PROSES = [StatusLaporan::Ditinjau, StatusLaporan::Diproses, StatusLaporan::Ditampung, StatusLaporan::DalamPembahasan];

    /**
     * @var list<StatusLaporan>
     */
    private const STATUS_DITOLAK = [StatusLaporan::Ditolak, StatusLaporan::TidakDapatDitindaklanjuti];

    /**
     * Ringkasan statistik periode 12 bulan terakhir beserta pembanding 12 bulan sebelumnya.
     *
     * @return array{
     *     total: int,
     *     tren_total_persen: float|null,
     *     tingkat_penyelesaian: float,
     *     penyelesaian_sebelumnya: float|null,
     *     tingkat_respon: float,
     *     rata_respon: array{nilai: float, satuan: string, label: string}|null,
     *     distribusi: array{selesai: int, proses: int, menunggu: int, ditolak: int},
     *     persen: array{selesai: float, proses: float, menunggu: float, ditolak: float}
     * }
     */
    public function ringkasan(): array
    {
        $akhir = now();
        $awal = $akhir->copy()->subYear();
        $awalSebelumnya = $awal->copy()->subYear();

        $jumlahPerStatus = $this->jumlahPerStatus($awal, $akhir);
        $total = $jumlahPerStatus->sum();

        $distribusi = [
            'selesai' => $this->jumlahStatus($jumlahPerStatus, self::STATUS_SELESAI),
            'proses' => $this->jumlahStatus($jumlahPerStatus, self::STATUS_PROSES),
            'menunggu' => $this->jumlahStatus($jumlahPerStatus, [StatusLaporan::Diajukan]),
            'ditolak' => $this->jumlahStatus($jumlahPerStatus, self::STATUS_DITOLAK),
        ];

        $jumlahSebelumnya = $this->jumlahPerStatus($awalSebelumnya, $awal);
        $totalSebelumnya = $jumlahSebelumnya->sum();

        return [
            'total' => $total,
            'tren_total_persen' => $totalSebelumnya > 0 ? round(($total - $totalSebelumnya) / $totalSebelumnya * 100, 1) : null,
            'tingkat_penyelesaian' => $this->persen($distribusi['selesai'], $total),
            'penyelesaian_sebelumnya' => $totalSebelumnya > 0
                ? $this->persen($this->jumlahStatus($jumlahSebelumnya, self::STATUS_SELESAI), $totalSebelumnya)
                : null,
            'tingkat_respon' => $this->persen($total - $distribusi['menunggu'], $total),
            'rata_respon' => $this->rataWaktuRespon($awal, $akhir),
            'distribusi' => $distribusi,
            'persen' => array_map(fn (int $jumlah) => $this->persen($jumlah, $total), $distribusi),
        ];
    }

    /**
     * @return Collection<string, int>
     */
    private function jumlahPerStatus(CarbonInterface $awal, CarbonInterface $akhir): Collection
    {
        return Pengaduan::query()
            ->whereBetween('created_at', [$awal, $akhir])
            ->toBase()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->map(fn ($jumlah) => (int) $jumlah);
    }

    /**
     * @param  Collection<string, int>  $jumlahPerStatus
     * @param  list<StatusLaporan>  $daftarStatus
     */
    private function jumlahStatus(Collection $jumlahPerStatus, array $daftarStatus): int
    {
        return (int) collect($daftarStatus)->sum(fn (StatusLaporan $status) => $jumlahPerStatus->get($status->value, 0));
    }

    private function persen(int $bagian, int $total): float
    {
        return $total > 0 ? round($bagian / $total * 100, 1) : 0.0;
    }

    /**
     * Rata-rata waktu sejak laporan dikirim hingga pertama kali ditanggapi petugas
     * (status pertama selain Diajukan).
     *
     * @return array{nilai: float, satuan: string, label: string}|null
     */
    private function rataWaktuRespon(CarbonInterface $awal, CarbonInterface $akhir): ?array
    {
        $laporan = Pengaduan::query()
            ->whereBetween('created_at', [$awal, $akhir])
            ->pluck('created_at', 'id');

        if ($laporan->isEmpty()) {
            return null;
        }

        $tanggapanPertama = RiwayatStatus::query()
            ->whereIn('pengaduan_id', $laporan->keys())
            ->where('status', '!=', StatusLaporan::Diajukan->value)
            ->toBase()
            ->selectRaw('pengaduan_id, MIN(created_at) as pertama')
            ->groupBy('pengaduan_id')
            ->pluck('pertama', 'pengaduan_id');

        $durasiDetik = $tanggapanPertama
            ->map(fn ($pertama, $id) => max(0, Carbon::parse($pertama)->getTimestamp() - $laporan[$id]->getTimestamp()))
            ->values();

        if ($durasiDetik->isEmpty()) {
            return null;
        }

        $rataDetik = $durasiDetik->avg();

        [$nilai, $satuan] = match (true) {
            $rataDetik < 3600 => [max(1, round($rataDetik / 60)), 'menit'],
            $rataDetik < 86400 => [round($rataDetik / 3600, 1), 'jam'],
            default => [round($rataDetik / 86400, 1), 'hari'],
        };

        $teksNilai = number_format($nilai, fmod($nilai, 1) === 0.0 ? 0 : 1, ',', '.');

        return ['nilai' => (float) $nilai, 'satuan' => $satuan, 'label' => $teksNilai.' '.ucfirst($satuan)];
    }
}
