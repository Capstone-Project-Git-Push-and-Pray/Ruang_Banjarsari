<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusAkun;
use App\Models\DataKependudukan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeninjauanPendaftaranTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // PBI #16: Daftar Pendaftaran Menunggu Tinjauan
    // =========================================================================

    public function test_admin_desa_dapat_mengakses_daftar_pendaftaran_menunggu_tinjauan(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get(route('admin.pendaftaran.index'));

        $response->assertStatus(200);
        $response->assertSee('Peninjauan Pendaftaran Warga');
        $response->assertSee('Antrean Verifikasi Pendaftar');
    }

    public function test_pengguna_bukan_admin_desa_tidak_dapat_mengakses_daftar_pendaftaran(): void
    {
        $warga = User::factory()->warga()->create();
        $kepalaDesa = User::factory()->kepalaDesa()->create();

        $this->actingAs($warga)->get(route('admin.pendaftaran.index'))->assertStatus(403);
        $this->actingAs($kepalaDesa)->get(route('admin.pendaftaran.index'))->assertStatus(403);

        auth()->logout();
        $this->get(route('admin.pendaftaran.index'))->assertRedirect('/login');
    }

    public function test_daftar_pendaftaran_terurut_dari_yang_terlama(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $wargaSedang = User::factory()->warga()->menungguTinjauan()->create([
            'nama_lengkap' => 'Warga Tengah',
            'created_at' => now()->subDays(2),
        ]);

        $wargaTerbaru = User::factory()->warga()->menungguTinjauan()->create([
            'nama_lengkap' => 'Warga Terbaru',
            'created_at' => now()->subHours(3),
        ]);

        $wargaTerlama = User::factory()->warga()->menungguTinjauan()->create([
            'nama_lengkap' => 'Warga Terlama',
            'created_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pendaftaran.index'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'WARGA TERLAMA',
            'WARGA TENGAH',
            'WARGA TERBARU',
        ]);
    }

    public function test_daftar_pendaftaran_menampilkan_nama_nik_dan_waktu_daftar(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $warga = User::factory()->warga()->menungguTinjauan()->create([
            'nama_lengkap' => 'Slamet Riyadi',
            'nik' => '3207011205900001',
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pendaftaran.index'));

        $response->assertStatus(200);
        $response->assertSee('SLAMET RIYADI');
        $response->assertSee('3207011205900001');
        $response->assertSee($warga->created_at->format('d M Y, H:i'));
    }

    // =========================================================================
    // PBI #17: Perbandingan Data Pendaftar dan Data Kependudukan
    // =========================================================================

    public function test_admin_desa_dapat_melihat_perbandingan_data_pendaftar_dan_kependudukan(): void
    {
        $admin = User::factory()->adminDesa()->create();

        DataKependudukan::factory()->create([
            'nik' => '3207011205900002',
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_lahir' => '1990-05-12',
            'alamat' => 'Jl. Merpati No. 10',
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
        ]);

        $warga = User::factory()->warga()->menungguTinjauan()->create([
            'nik' => '3207011205900002',
            'nama_lengkap' => 'Budi Santoso', // sama
            'tanggal_lahir' => '1990-05-12', // sama
            'alamat' => 'Jl. Elang No. 99', // beda
            'rt' => '001',
            'rw' => '002',
            'dusun' => 'Dusun Krajan',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pendaftaran.show', $warga));

        $response->assertStatus(200);
        $response->assertSee('Perbandingan Data Pendaftar vs Master Kependudukan');
        $response->assertSee('BUDI SANTOSO');
        $response->assertSee('Jl. Elang No. 99');
        $response->assertSee('Jl. Merpati No. 10');
        $response->assertSee('Cocok');
        $response->assertSee('Berbeda');
    }

    public function test_halaman_detail_menampilkan_keterangan_jika_nik_tidak_ditemukan(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $warga = User::factory()->warga()->menungguTinjauan()->create([
            'nik' => '3207019999999999',
            'nama_lengkap' => 'Warga Tanpa NIK Terdaftar',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pendaftaran.show', $warga));

        $response->assertStatus(200);
        $response->assertSee('NIK Pendaftar Tidak Ditemukan dalam Data Kependudukan Desa');
        $response->assertSee('3207019999999999');
    }

    public function test_warga_dan_kepala_desa_tidak_bisa_mengakses_halaman_perbandingan(): void
    {
        $warga = User::factory()->warga()->create();
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $targetWarga = User::factory()->warga()->menungguTinjauan()->create();

        $this->actingAs($warga)->get(route('admin.pendaftaran.show', $targetWarga))->assertStatus(403);
        $this->actingAs($kepalaDesa)->get(route('admin.pendaftaran.show', $targetWarga))->assertStatus(403);
    }

    // =========================================================================
    // PBI #18: Persetujuan dan Penolakan Pendaftaran
    // =========================================================================

    public function test_admin_desa_dapat_menyetujui_pendaftaran_warga(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $warga = User::factory()->warga()->menungguTinjauan()->create([
            'nama_lengkap' => 'Indra Lesmana',
            'nik' => '3207011205900003',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pendaftaran.setujui', $warga));

        $response->assertRedirect(route('admin.pendaftaran.index'));
        $response->assertSessionHas('success');

        $warga->refresh();
        $this->assertSame(StatusAkun::Aktif, $warga->status_akun);
        $this->assertSame($admin->id, $warga->ditinjau_oleh);
        $this->assertNotNull($warga->ditinjau_pada);
        $this->assertNull($warga->alasan_penolakan);
    }

    public function test_admin_desa_dapat_menolak_pendaftaran_warga_dengan_alasan_wajib(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $warga = User::factory()->warga()->menungguTinjauan()->create([
            'nama_lengkap' => 'Rian Hidayat',
            'nik' => '3207011205900004',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pendaftaran.tolak', $warga), [
            'alasan_penolakan' => 'Data KTP buram dan nama tidak cocok dengan buku induk kependudukan.',
        ]);

        $response->assertRedirect(route('admin.pendaftaran.index'));
        $response->assertSessionHas('success');

        $warga->refresh();
        $this->assertSame(StatusAkun::Ditolak, $warga->status_akun);
        $this->assertSame($admin->id, $warga->ditinjau_oleh);
        $this->assertNotNull($warga->ditinjau_pada);
        $this->assertSame('Data KTP buram dan nama tidak cocok dengan buku induk kependudukan.', $warga->alasan_penolakan);
    }

    public function test_penolakan_pendaftaran_wajib_menyertakan_alasan(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $warga = User::factory()->warga()->menungguTinjauan()->create();

        $response = $this->actingAs($admin)->post(route('admin.pendaftaran.tolak', $warga), [
            'alasan_penolakan' => '',
        ]);

        $response->assertSessionHasErrors('alasan_penolakan');

        $warga->refresh();
        $this->assertSame(StatusAkun::MenungguTinjauan, $warga->status_akun);
    }

    public function test_alasan_penolakan_admin_tampil_pada_halaman_status_warga(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $warga = User::factory()->warga()->menungguTinjauan()->create();

        $this->actingAs($admin)->post(route('admin.pendaftaran.tolak', $warga), [
            'alasan_penolakan' => 'Nama ibu kandung dan e-KTP tidak sesuai dengan data SIAK.',
        ]);

        $warga->refresh();

        // Login sebagai warga yang ditolak untuk melihat halaman status pendaftaran
        $response = $this->actingAs($warga)->get(route('pendaftaran.status'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun Ditolak');
        $response->assertSee('Nama ibu kandung dan e-KTP tidak sesuai dengan data SIAK.');
    }

    public function test_bukan_admin_desa_tidak_bisa_menyetujui_atau_menolak_pendaftaran(): void
    {
        $warga = User::factory()->warga()->create();
        $kepalaDesa = User::factory()->kepalaDesa()->create();
        $targetWarga = User::factory()->warga()->menungguTinjauan()->create();

        $this->actingAs($warga)
            ->post(route('admin.pendaftaran.setujui', $targetWarga))
            ->assertStatus(403);

        $this->actingAs($kepalaDesa)
            ->post(route('admin.pendaftaran.setujui', $targetWarga))
            ->assertStatus(403);

        $this->actingAs($warga)
            ->post(route('admin.pendaftaran.tolak', $targetWarga), ['alasan_penolakan' => 'Coba tolak'])
            ->assertStatus(403);

        $this->actingAs($kepalaDesa)
            ->post(route('admin.pendaftaran.tolak', $targetWarga), ['alasan_penolakan' => 'Coba tolak'])
            ->assertStatus(403);
    }
}
