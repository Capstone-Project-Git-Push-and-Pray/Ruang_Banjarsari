<?php

namespace App\Enums;

/**
 * Alasan penghapusan konten aspirasi/komentar oleh Admin Desa (PBI #45).
 */
enum AlasanModerasi: string
{
    case Spam = 'spam';
    case KataKasar = 'kata_kasar';
    case InformasiPalsu = 'informasi_palsu';
    case DataPribadi = 'data_pribadi';
    case TidakRelevan = 'tidak_relevan';
    case Duplikat = 'duplikat';
    case Lainnya = 'lainnya';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam atau promosi',
            self::KataKasar => 'Kata kasar, hinaan, atau ujaran kebencian',
            self::InformasiPalsu => 'Informasi palsu atau menyesatkan',
            self::DataPribadi => 'Memuat data pribadi orang lain',
            self::TidakRelevan => 'Tidak relevan dengan kepentingan desa',
            self::Duplikat => 'Duplikat konten yang sudah ada',
            self::Lainnya => 'Lainnya',
        };
    }
}
