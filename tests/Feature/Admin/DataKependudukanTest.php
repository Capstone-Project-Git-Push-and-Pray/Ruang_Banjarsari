<?php

namespace Tests\Feature\Admin;

use App\Models\DataKependudukan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataKependudukanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tamu/pengguna belum login diarahkan ke login.
     */
    public function test_tamu_tidak_dapat_mengakses_halaman_master_data_kependudukan(): void
    {
        $response = $this->get(route('admin.kependudukan.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Warga biasa tidak boleh mengakses master data kependudukan (403).
     */
    public function test_warga_tidak_dapat_mengakses_halaman_master_data_kependudukan(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('admin.kependudukan.index'));

        $response->assertStatus(403);
    }

    /**
     * Admin Desa dapat membuka dan melihat master data kependudukan.
     */
    public function test_admin_dapat_melihat_master_data_kependudukan(): void
    {
        $admin = User::factory()->adminDesa()->create();

        DataKependudukan::factory()->create([
            'nik' => '3201123456780001',
            'nama_lengkap' => 'SLAMET RIYADI',
            'dusun' => 'Dusun Krajan',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kependudukan.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Kependudukan');
        $response->assertSee('3201123456780001');
        $response->assertSee('SLAMET RIYADI');
        $response->assertSee('Dusun Krajan');
    }

    /**
     * Fitur pencarian berdasarkan NIK atau Nama Lengkap berfungsi.
     */
    public function test_admin_dapat_mencari_penduduk_berdasarkan_nik_atau_nama(): void
    {
        $admin = User::factory()->adminDesa()->create();

        DataKependudukan::factory()->create([
            'nik' => '3201999900000001',
            'nama_lengkap' => 'AGUS HERMAWAN',
        ]);

        DataKependudukan::factory()->create([
            'nik' => '3201888800000002',
            'nama_lengkap' => 'RATNA SARI',
        ]);

        // Cari berdasarkan nama
        $responseNama = $this->actingAs($admin)->get(route('admin.kependudukan.index', ['search' => 'AGUS']));
        $responseNama->assertStatus(200);
        $responseNama->assertSee('AGUS HERMAWAN');
        $responseNama->assertDontSee('RATNA SARI');

        // Cari berdasarkan NIK
        $responseNik = $this->actingAs($admin)->get(route('admin.kependudukan.index', ['search' => '3201888800000002']));
        $responseNik->assertStatus(200);
        $responseNik->assertSee('RATNA SARI');
        $responseNik->assertDontSee('AGUS HERMAWAN');
    }

    /**
     * Filter berdasarkan Dusun.
     */
    public function test_admin_dapat_memfilter_penduduk_berdasarkan_dusun(): void
    {
        $admin = User::factory()->adminDesa()->create();

        DataKependudukan::factory()->create([
            'nik' => '3201111100000001',
            'nama_lengkap' => 'WARGA KRAJAN',
            'dusun' => 'Dusun Krajan',
        ]);

        DataKependudukan::factory()->create([
            'nik' => '3201222200000002',
            'nama_lengkap' => 'WARGA TIMUR',
            'dusun' => 'Dusun Banjarsari Timur',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kependudukan.index', ['dusun' => 'Dusun Krajan']));
        $response->assertStatus(200);
        $response->assertSee('WARGA KRAJAN');
        $response->assertDontSee('WARGA TIMUR');
    }

    /**
     * Filter berdasarkan status akun SILAFA.
     */
    public function test_admin_dapat_memfilter_penduduk_berdasarkan_status_akun(): void
    {
        $admin = User::factory()->adminDesa()->create();

        // Penduduk yang sudah punya akun aktif
        $aktif = DataKependudukan::factory()->create([
            'nik' => '3201333300000001',
            'nama_lengkap' => 'WARGA AKTIF',
        ]);
        User::factory()->warga()->create([
            'nik' => $aktif->nik,
            'nama_lengkap' => $aktif->nama_lengkap,
        ]);

        // Penduduk yang belum punya akun
        DataKependudukan::factory()->create([
            'nik' => '3201444400000002',
            'nama_lengkap' => 'WARGA BELUM AKUN',
        ]);

        $responseAktif = $this->actingAs($admin)->get(route('admin.kependudukan.index', ['status_akun' => 'aktif']));
        $responseAktif->assertStatus(200);
        $responseAktif->assertSee('WARGA AKTIF');
        $responseAktif->assertDontSee('WARGA BELUM AKUN');

        $responseBelum = $this->actingAs($admin)->get(route('admin.kependudukan.index', ['status_akun' => 'belum']));
        $responseBelum->assertStatus(200);
        $responseBelum->assertSee('WARGA BELUM AKUN');
        $responseBelum->assertDontSee('WARGA AKTIF');
    }

    /**
     * Admin Desa dapat menambahkan data penduduk baru.
     */
    public function test_admin_dapat_menambah_data_penduduk_baru(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->post(route('admin.kependudukan.store'), [
            'nik' => '3201555566667777',
            'nama_lengkap' => 'Joko Widodo Santoso',
            'tanggal_lahir' => '1990-05-15',
            'alamat' => 'Jl. Kenanga No. 45',
            'rt' => '2',
            'rw' => '1',
            'dusun' => 'Dusun Krajan',
        ]);

        $response->assertRedirect(route('admin.kependudukan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('data_kependudukan', [
            'nik' => '3201555566667777',
            'nama_lengkap' => 'JOKO WIDODO SANTOSO',
            'rt' => '002',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);
    }

    /**
     * Validasi saat menambah data penduduk (NIK harus 16 digit dan unik).
     */
    public function test_validasi_saat_menambah_data_penduduk(): void
    {
        $admin = User::factory()->adminDesa()->create();

        DataKependudukan::factory()->create(['nik' => '3201000000000001']);

        // NIK duplicate
        $responseDuplicate = $this->actingAs($admin)->post(route('admin.kependudukan.store'), [
            'nik' => '3201000000000001',
            'nama_lengkap' => 'Nama Baru',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);
        $responseDuplicate->assertSessionHasErrors(['nik']);

        // NIK bukan 16 digit
        $responseShort = $this->actingAs($admin)->post(route('admin.kependudukan.store'), [
            'nik' => '12345',
            'nama_lengkap' => 'Nama Baru',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'rt' => '001',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
        ]);
        $responseShort->assertSessionHasErrors(['nik']);
    }

    /**
     * Admin Desa dapat memperbarui data penduduk.
     */
    public function test_admin_dapat_memperbarui_data_penduduk(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $penduduk = DataKependudukan::factory()->create([
            'nik' => '3201777788889999',
            'nama_lengkap' => 'NAMA LAMA',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.kependudukan.update', $penduduk), [
            'nik' => '3201777788889999',
            'nama_lengkap' => 'NAMA DIPERBARUI',
            'tanggal_lahir' => '1992-03-20',
            'alamat' => 'Jl. Baru No. 99',
            'rt' => '003',
            'rw' => '002',
            'dusun' => 'Dusun Mulyasari',
        ]);

        $response->assertRedirect(route('admin.kependudukan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('data_kependudukan', [
            'id' => $penduduk->id,
            'nama_lengkap' => 'NAMA DIPERBARUI',
            'dusun' => 'Dusun Mulyasari',
        ]);
    }

    /**
     * Admin Desa dapat menghapus data penduduk.
     */
    public function test_admin_dapat_menghapus_data_penduduk(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $penduduk = DataKependudukan::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.kependudukan.destroy', $penduduk));

        $response->assertRedirect(route('admin.kependudukan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('data_kependudukan', [
            'id' => $penduduk->id,
        ]);
    }
}
