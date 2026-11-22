<?php

namespace App\Enums;

/**
 * Jenis konten publik yang dapat dimoderasi Admin Desa.
 */
enum JenisKonten: string
{
    case Aspirasi = 'aspirasi';
    case Komentar = 'komentar';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Aspirasi => 'Aspirasi',
            self::Komentar => 'Komentar',
        };
    }
}
