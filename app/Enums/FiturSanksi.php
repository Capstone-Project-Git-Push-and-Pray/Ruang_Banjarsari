<?php

namespace App\Enums;

/**
 * Fitur warga yang dapat dibatasi oleh sanksi (PBI #46, PBI #47).
 */
enum FiturSanksi: string
{
    case Aspirasi = 'aspirasi';
    case KomentarPublik = 'komentar_publik';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Aspirasi => 'Aspirasi',
            self::KomentarPublik => 'Komentar Publik',
        };
    }

    /**
     * Penjelasan fitur yang dibatasi selama sanksi berlaku (PBI #48).
     */
    public function pembatasan(): string
    {
        return match ($this) {
            self::Aspirasi => 'Tidak dapat mengajukan aspirasi baru. Pengaduan tetap dapat diajukan.',
            self::KomentarPublik => 'Tidak dapat berkomentar pada aspirasi. Komentar pada pengaduan milik sendiri tetap dapat dikirim.',
        };
    }

    /**
     * Fitur yang terdampak saat konten jenis ini dihapus karena pelanggaran.
     */
    public static function untukKonten(JenisKonten $jenisKonten): self
    {
        return match ($jenisKonten) {
            JenisKonten::Aspirasi => self::Aspirasi,
            JenisKonten::Komentar => self::KomentarPublik,
        };
    }

    /**
     * Lama pembatasan untuk tingkat pelanggaran tertentu.
     *
     * Aspirasi (PBI #46): peringatan, 7 hari, 30 hari, lalu permanen.
     * Komentar publik (PBI #47): selalu sementara, 3 hari, 7 hari, lalu 30 hari.
     *
     * @return int|null Jumlah hari; 0 berarti peringatan tanpa pembatasan, null berarti permanen.
     */
    public function durasiHari(int $tingkat): ?int
    {
        return match ($this) {
            self::Aspirasi => match (true) {
                $tingkat <= 1 => 0,
                $tingkat === 2 => 7,
                $tingkat === 3 => 30,
                default => null,
            },
            self::KomentarPublik => match (true) {
                $tingkat <= 1 => 3,
                $tingkat === 2 => 7,
                default => 30,
            },
        };
    }
}
