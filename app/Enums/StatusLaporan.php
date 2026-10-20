<?php

namespace App\Enums;

/**
 * Status laporan pengaduan/aspirasi.
 */
enum StatusLaporan: string
{
    case Diajukan = 'diajukan';
    case Ditinjau = 'ditinjau';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::Ditinjau => 'Ditinjau',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak',
        };
    }
}
