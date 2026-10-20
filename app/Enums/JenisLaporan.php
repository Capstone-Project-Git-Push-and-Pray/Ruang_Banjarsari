<?php

namespace App\Enums;

/**
 * Jenis laporan yang dapat diajukan warga.
 */
enum JenisLaporan: string
{
    case Pengaduan = 'pengaduan';
    case Aspirasi = 'aspirasi';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pengaduan => 'Pengaduan',
            self::Aspirasi => 'Aspirasi',
        };
    }
}
