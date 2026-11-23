<?php

namespace App\Enums;

/**
 * Status sanksi warga pada saat ini (PBI #48, PBI #49).
 */
enum StatusSanksi: string
{
    case Berlaku = 'berlaku';
    case Peringatan = 'peringatan';
    case Berakhir = 'berakhir';
    case Diakhiri = 'diakhiri';
    case Dibatalkan = 'dibatalkan';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Berlaku => 'Berlaku',
            self::Peringatan => 'Peringatan',
            self::Berakhir => 'Berakhir',
            self::Diakhiri => 'Dibuka Kembali',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Kelas gaya warna Tailwind untuk badge status sanksi.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Berlaku => 'bg-red-50 text-red-700 border-red-200',
            self::Peringatan => 'bg-amber-50 text-amber-800 border-amber-200',
            self::Berakhir, self::Diakhiri => 'bg-gray-100 text-gray-700 border-gray-200',
            self::Dibatalkan => 'bg-sky-50 text-sky-700 border-sky-200',
        };
    }
}
