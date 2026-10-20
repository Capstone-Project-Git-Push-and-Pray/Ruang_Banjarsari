<?php

namespace App\Enums;

/**
 * Peran pengguna dalam sistem.
 */
enum Role: string
{
    case Warga = 'warga';
    case AdminDesa = 'admin_desa';
    case KepalaDesa = 'kepala_desa';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Warga',
            self::AdminDesa => 'Admin Desa',
            self::KepalaDesa => 'Kepala Desa',
        };
    }
}
