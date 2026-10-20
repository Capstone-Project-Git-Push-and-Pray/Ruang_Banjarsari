<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Models\DataKependudukan;
use App\Models\Kategori;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_berjalan_tanpa_error_dan_jumlah_data_sesuai(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verifikasi jumlah kategori awal
        $this->assertDatabaseCount('kategori', 7);
        $this->assertTrue(Kategori::where('nama', 'Jalan Rusak')->exists());
        $this->assertTrue(Kategori::where('nama', 'Fasilitas Umum')->exists());

        // Verifikasi jumlah data kependudukan fiktif desa
        $this->assertDatabaseCount('data_kependudukan', 30);
        $this->assertTrue(DataKependudukan::where('nik', '9999000000000001')->exists());

        // Verifikasi akun pengguna bawaan seeder
        $this->assertDatabaseCount('users', 4);

        // Verifikasi Admin Desa
        $admin = User::where('email', 'admin@desa-banjarsari.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame(Role::AdminDesa, $admin->role);
        $this->assertSame(StatusAkun::Aktif, $admin->status_akun);

        // Verifikasi Kepala Desa
        $kades = User::where('email', 'kades@desa-banjarsari.test')->first();
        $this->assertNotNull($kades);
        $this->assertSame(Role::KepalaDesa, $kades->role);
        $this->assertSame(StatusAkun::Aktif, $kades->status_akun);

        // Verifikasi Warga Aktif
        $wargaAktif = User::where('nik', '9999000000000001')->first();
        $this->assertNotNull($wargaAktif);
        $this->assertSame(Role::Warga, $wargaAktif->role);
        $this->assertSame(StatusAkun::Aktif, $wargaAktif->status_akun);

        // Verifikasi Warga Menunggu Tinjauan
        $wargaPending = User::where('nik', '9999000000000002')->first();
        $this->assertNotNull($wargaPending);
        $this->assertSame(Role::Warga, $wargaPending->role);
        $this->assertSame(StatusAkun::MenungguTinjauan, $wargaPending->status_akun);
    }
}
