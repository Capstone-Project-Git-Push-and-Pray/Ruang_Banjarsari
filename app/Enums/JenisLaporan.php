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

    /**
     * Kelas gaya warna Tailwind untuk badge jenis laporan.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pengaduan => 'bg-rose-50 text-rose-700 border-rose-200',
            self::Aspirasi => 'bg-sky-50 text-sky-700 border-sky-200',
        };
    }
}
