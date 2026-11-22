<?php

namespace App\Enums;

/**
 * Status akun pengguna.
 */
enum StatusAkun: string
{
    case Aktif = 'aktif';
    case MenungguTinjauan = 'menunggu_tinjauan';
    case Ditolak = 'ditolak';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::MenungguTinjauan => 'Menunggu Tinjauan',
            self::Ditolak => 'Ditolak',
        };
    }

    /**
     * Kelas gaya warna Tailwind untuk badge status akun.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Aktif => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::MenungguTinjauan => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Ditolak => 'bg-red-100 text-red-800 border-red-200',
        };
    }
}
