<?php

namespace App\Enums;

/**
 * Tindakan moderasi Admin Desa terhadap konten publik (PBI #44, PBI #45).
 */
enum AksiModerasi: string
{
    case Sembunyikan = 'sembunyikan';
    case Tampilkan = 'tampilkan';
    case Hapus = 'hapus';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sembunyikan => 'Disembunyikan',
            self::Tampilkan => 'Ditampilkan Kembali',
            self::Hapus => 'Dihapus',
        };
    }

    /**
     * Kelas gaya warna Tailwind untuk badge tindakan moderasi.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Sembunyikan => 'bg-amber-50 text-amber-800 border-amber-200',
            self::Tampilkan => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            self::Hapus => 'bg-red-50 text-red-700 border-red-200',
        };
    }
}
