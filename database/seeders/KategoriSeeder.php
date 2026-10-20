<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Jalankan database seeds untuk kategori laporan.
     */
    public function run(): void
    {
        $kategoriList = [
            'Jalan Rusak',
            'Fasilitas Umum',
            'Kebersihan dan Lingkungan',
            'Penerangan Jalan',
            'Saluran Air dan Drainase',
            'Keamanan dan Ketertiban',
            'Lainnya',
        ];

        foreach ($kategoriList as $nama) {
            Kategori::firstOrCreate(
                ['nama' => $nama],
                ['aktif' => true]
            );
        }
    }
}
