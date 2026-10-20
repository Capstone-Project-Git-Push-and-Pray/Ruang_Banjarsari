<?php

namespace Database\Seeders;

use App\Models\DataKependudukan;
use Illuminate\Database\Seeder;

class DataKependudukanSeeder extends Seeder
{
    /**
     * Jalankan database seeds untuk data kependudukan desa fiktif.
     */
    public function run(): void
    {
        $pendudukFiktif = [
            ['nama_lengkap' => 'Budi Santoso', 'tanggal_lahir' => '1985-04-12', 'alamat' => 'Jl. Merpati No. 12', 'rt' => '001', 'rw' => '001', 'dusun' => 'Dusun Krajan'],
            ['nama_lengkap' => 'Siti Aminah', 'tanggal_lahir' => '1990-08-25', 'alamat' => 'Jl. Mawar Indah No. 5', 'rt' => '001', 'rw' => '001', 'dusun' => 'Dusun Krajan'],
            ['nama_lengkap' => 'Agus Setiawan', 'tanggal_lahir' => '1982-11-03', 'alamat' => 'Jl. Kenanga No. 8', 'rt' => '002', 'rw' => '001', 'dusun' => 'Dusun Krajan'],
            ['nama_lengkap' => 'Dewi Lestari', 'tanggal_lahir' => '1995-02-14', 'alamat' => 'Jl. Dahlia No. 3', 'rt' => '002', 'rw' => '001', 'dusun' => 'Dusun Krajan'],
            ['nama_lengkap' => 'Eko Prasetyo', 'tanggal_lahir' => '1988-06-19', 'alamat' => 'Jl. Melati No. 17', 'rt' => '003', 'rw' => '001', 'dusun' => 'Dusun Krajan'],
            ['nama_lengkap' => 'Sri Wahyuni', 'tanggal_lahir' => '1979-12-30', 'alamat' => 'Jl. Cempaka No. 2', 'rt' => '003', 'rw' => '001', 'dusun' => 'Dusun Krajan'],
            ['nama_lengkap' => 'Bambang Supriyanto', 'tanggal_lahir' => '1975-03-08', 'alamat' => 'Jl. Flamboyan No. 22', 'rt' => '004', 'rw' => '002', 'dusun' => 'Dusun Banjarsari Timur'],
            ['nama_lengkap' => 'Rina Kartika', 'tanggal_lahir' => '1992-07-11', 'alamat' => 'Jl. Anggrek No. 14', 'rt' => '004', 'rw' => '002', 'dusun' => 'Dusun Banjarsari Timur'],
            ['nama_lengkap' => 'Hadi Nugroho', 'tanggal_lahir' => '1984-09-05', 'alamat' => 'Jl. Teratai No. 9', 'rt' => '005', 'rw' => '002', 'dusun' => 'Dusun Banjarsari Timur'],
            ['nama_lengkap' => 'Nurul Hidayati', 'tanggal_lahir' => '1996-01-22', 'alamat' => 'Jl. Kamboja No. 11', 'rt' => '005', 'rw' => '002', 'dusun' => 'Dusun Banjarsari Timur'],
            ['nama_lengkap' => 'Joko Susilo', 'tanggal_lahir' => '1980-05-15', 'alamat' => 'Jl. Anyelir No. 6', 'rt' => '006', 'rw' => '002', 'dusun' => 'Dusun Banjarsari Timur'],
            ['nama_lengkap' => 'Tri Handayani', 'tanggal_lahir' => '1987-10-18', 'alamat' => 'Jl. Asoka No. 20', 'rt' => '006', 'rw' => '002', 'dusun' => 'Dusun Banjarsari Timur'],
            ['nama_lengkap' => 'Ahmad Fauzi', 'tanggal_lahir' => '1991-03-27', 'alamat' => 'Jl. Bougenville No. 4', 'rt' => '001', 'rw' => '003', 'dusun' => 'Dusun Banjarsari Barat'],
            ['nama_lengkap' => 'Yuni Astuti', 'tanggal_lahir' => '1994-08-09', 'alamat' => 'Jl. Sakura No. 18', 'rt' => '001', 'rw' => '003', 'dusun' => 'Dusun Banjarsari Barat'],
            ['nama_lengkap' => 'Rudi Hermawan', 'tanggal_lahir' => '1983-12-01', 'alamat' => 'Jl. Kenari No. 7', 'rt' => '002', 'rw' => '003', 'dusun' => 'Dusun Banjarsari Barat'],
            ['nama_lengkap' => 'Indah Permatasari', 'tanggal_lahir' => '1997-04-16', 'alamat' => 'Jl. Merpati Putih No. 15', 'rt' => '002', 'rw' => '003', 'dusun' => 'Dusun Banjarsari Barat'],
            ['nama_lengkap' => 'Surya Saputra', 'tanggal_lahir' => '1986-06-23', 'alamat' => 'Jl. Rajawali No. 10', 'rt' => '003', 'rw' => '003', 'dusun' => 'Dusun Banjarsari Barat'],
            ['nama_lengkap' => 'Ratna Juwita', 'tanggal_lahir' => '1993-11-29', 'alamat' => 'Jl. Garuda No. 1', 'rt' => '003', 'rw' => '003', 'dusun' => 'Dusun Banjarsari Barat'],
            ['nama_lengkap' => 'Hendro Utomo', 'tanggal_lahir' => '1978-02-17', 'alamat' => 'Jl. Gelatik No. 13', 'rt' => '004', 'rw' => '004', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Lestari Handayani', 'tanggal_lahir' => '1989-05-04', 'alamat' => 'Jl. Kepodang No. 21', 'rt' => '004', 'rw' => '004', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Sugeng Priyadi', 'tanggal_lahir' => '1981-10-10', 'alamat' => 'Jl. Kutilang No. 16', 'rt' => '005', 'rw' => '004', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Wulandari Putri', 'tanggal_lahir' => '1998-07-07', 'alamat' => 'Jl. Beo No. 8', 'rt' => '005', 'rw' => '004', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Aris Munandar', 'tanggal_lahir' => '1985-09-14', 'alamat' => 'Jl. Murai No. 25', 'rt' => '006', 'rw' => '004', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Fitriani', 'tanggal_lahir' => '1992-04-01', 'alamat' => 'Jl. Perkutut No. 2', 'rt' => '006', 'rw' => '004', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Danang Wicaksono', 'tanggal_lahir' => '1987-01-19', 'alamat' => 'Jl. Kakaktua No. 19', 'rt' => '001', 'rw' => '005', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Endang Sulastri', 'tanggal_lahir' => '1976-08-13', 'alamat' => 'Jl. Cendrawasih No. 27', 'rt' => '001', 'rw' => '005', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Bayu Pratama', 'tanggal_lahir' => '1999-12-05', 'alamat' => 'Jl. Jalak No. 30', 'rt' => '002', 'rw' => '005', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Anita Rahmawati', 'tanggal_lahir' => '1990-03-31', 'alamat' => 'Jl. Pipit No. 33', 'rt' => '002', 'rw' => '005', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Rahmat Hidayat', 'tanggal_lahir' => '1984-06-08', 'alamat' => 'Jl. Walet No. 5', 'rt' => '003', 'rw' => '005', 'dusun' => 'Dusun Banjarsari Selatan'],
            ['nama_lengkap' => 'Diah Pitaloka', 'tanggal_lahir' => '1993-09-20', 'alamat' => 'Jl. Kolibri No. 9', 'rt' => '003', 'rw' => '005', 'dusun' => 'Dusun Banjarsari Selatan'],
        ];

        foreach ($pendudukFiktif as $index => $data) {
            $nomorUrut = str_pad((string) ($index + 1), 12, '0', STR_PAD_LEFT);
            $nik = '9999'.$nomorUrut;

            DataKependudukan::firstOrCreate(
                ['nik' => $nik],
                [
                    'nama_lengkap' => $data['nama_lengkap'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'alamat' => $data['alamat'],
                    'rt' => $data['rt'],
                    'rw' => $data['rw'],
                    'dusun' => $data['dusun'],
                ]
            );
        }
    }
}
