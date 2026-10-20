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
}
