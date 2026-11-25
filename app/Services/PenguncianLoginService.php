<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Penangguhan login bertahap per akun (per NIK / email), bukan per IP,
 * agar penangguhan tidak bisa dihindari dengan berganti perangkat.
 *
 * Setiap 3 kali gagal berturut-turut, akun ditangguhkan dengan durasi yang
 * meningkat (5, 15, lalu 30 menit). Riwayat tingkat penangguhan direset
 * setelah login berhasil atau 24 jam tanpa kegagalan.
 */
class PenguncianLoginService
{
    /**
     * Jumlah percobaan gagal sebelum akun ditangguhkan.
     */
    public const MAKS_PERCOBAAN = 3;

    /**
     * Durasi penangguhan (menit) untuk tiap tingkat; tingkat terakhir dipakai seterusnya.
     *
     * @var list<int>
     */
    public const DURASI_MENIT = [5, 15, 30];

    /**
     * Lama riwayat kegagalan disimpan sebelum direset otomatis (detik).
     */
    private const MASA_RIWAYAT_DETIK = 86400;

    /**
     * Sisa detik penangguhan untuk identitas login, atau 0 bila tidak ditangguhkan.
     */
    public function sisaDetikTertangguh(string $identitas): int
    {
        $berakhirPada = (int) Cache::get($this->kunci($identitas, 'tangguh'), 0);

        return max(0, $berakhirPada - now()->getTimestamp());
    }

    /**
     * Catat satu percobaan login gagal dan tangguhkan akun bila batas tercapai.
     *
     * @return array{sisa_percobaan: int, detik_tertangguh: int}
     */
    public function catatGagal(string $identitas): array
    {
        $kunciGagal = $this->kunci($identitas, 'gagal');
        $kunciTingkat = $this->kunci($identitas, 'tingkat');

        $jumlahGagal = (int) Cache::get($kunciGagal, 0) + 1;

        if ($jumlahGagal < self::MAKS_PERCOBAAN) {
            Cache::put($kunciGagal, $jumlahGagal, self::MASA_RIWAYAT_DETIK);

            return [
                'sisa_percobaan' => self::MAKS_PERCOBAAN - $jumlahGagal,
                'detik_tertangguh' => 0,
            ];
        }

        $tingkat = (int) Cache::get($kunciTingkat, 0) + 1;
        $durasiDetik = $this->durasiMenit($tingkat) * 60;

        Cache::put($kunciTingkat, $tingkat, self::MASA_RIWAYAT_DETIK);
        Cache::put($this->kunci($identitas, 'tangguh'), now()->getTimestamp() + $durasiDetik, $durasiDetik);
        Cache::forget($kunciGagal);

        return [
            'sisa_percobaan' => 0,
            'detik_tertangguh' => $durasiDetik,
        ];
    }

    /**
     * Hapus seluruh riwayat kegagalan setelah login berhasil.
     */
    public function bersihkan(string $identitas): void
    {
        foreach (['gagal', 'tingkat', 'tangguh'] as $jenis) {
            Cache::forget($this->kunci($identitas, $jenis));
        }
    }

    /**
     * Durasi penangguhan (menit) untuk tingkat tertentu.
     */
    public function durasiMenit(int $tingkat): int
    {
        $indeks = min(max($tingkat, 1), count(self::DURASI_MENIT)) - 1;

        return self::DURASI_MENIT[$indeks];
    }

    private function kunci(string $identitas, string $jenis): string
    {
        return 'login-penangguhan:'.$jenis.':'.sha1($identitas);
    }
}
