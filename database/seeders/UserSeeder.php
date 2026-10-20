<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Models\DataKependudukan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan database seeds untuk akun pengguna awal.
     */
    public function run(): void
    {
        // Password default development: "password"
        $defaultPassword = Hash::make(env('DEV_USER_PASSWORD', 'password'));

        // 1. Admin Desa
        User::firstOrCreate(
            ['email' => 'admin@desa-banjarsari.test'],
            [
                'nama_lengkap' => 'Administrator Desa Banjarsari',
                'password' => $defaultPassword,
                'role' => Role::AdminDesa,
                'status_akun' => StatusAkun::Aktif,
                'email_verified_at' => now(),
                'wajib_ganti_password' => false,
            ]
        );

        // 2. Kepala Desa
        User::firstOrCreate(
            ['email' => 'kades@desa-banjarsari.test'],
            [
                'nama_lengkap' => 'Kepala Desa Banjarsari',
                'password' => $defaultPassword,
                'role' => Role::KepalaDesa,
                'status_akun' => StatusAkun::Aktif,
                'email_verified_at' => now(),
                'wajib_ganti_password' => false,
            ]
        );

        // Ambil data kependudukan fiktif untuk warga contoh
        $penduduk1 = DataKependudukan::where('nik', '9999000000000001')->first();
        $penduduk2 = DataKependudukan::where('nik', '9999000000000002')->first();

        // 3. Warga Contoh 1: Berstatus Aktif
        if ($penduduk1) {
            User::firstOrCreate(
                ['nik' => $penduduk1->nik],
                [
                    'nama_lengkap' => $penduduk1->nama_lengkap,
                    'tanggal_lahir' => $penduduk1->tanggal_lahir,
                    'alamat' => $penduduk1->alamat,
                    'rt' => $penduduk1->rt,
                    'rw' => $penduduk1->rw,
                    'dusun' => $penduduk1->dusun,
                    'email' => 'warga.aktif@desa-banjarsari.test',
                    'telepon' => '081234567890',
                    'whatsapp' => '081234567890',
                    'password' => $defaultPassword,
                    'role' => Role::Warga,
                    'status_akun' => StatusAkun::Aktif,
                    'persetujuan_pada' => now(),
                    'email_verified_at' => now(),
                    'wajib_ganti_password' => false,
                ]
            );
        }

        // 4. Warga Contoh 2: Berstatus Menunggu Tinjauan
        if ($penduduk2) {
            User::firstOrCreate(
                ['nik' => $penduduk2->nik],
                [
                    'nama_lengkap' => $penduduk2->nama_lengkap,
                    'tanggal_lahir' => $penduduk2->tanggal_lahir,
                    'alamat' => $penduduk2->alamat,
                    'rt' => $penduduk2->rt,
                    'rw' => $penduduk2->rw,
                    'dusun' => $penduduk2->dusun,
                    'email' => 'warga.pending@desa-banjarsari.test',
                    'telepon' => '081234567891',
                    'whatsapp' => '081234567891',
                    'password' => $defaultPassword,
                    'role' => Role::Warga,
                    'status_akun' => StatusAkun::MenungguTinjauan,
                    'email_verified_at' => null,
                    'wajib_ganti_password' => false,
                ]
            );
        }
    }
}
