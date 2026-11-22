<?php

namespace Tests\Feature\Admin;

use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PBI #30: Admin Desa dapat melihat daftar kategori laporan.
     */
    public function test_admin_dapat_melihat_daftar_kategori(): void
    {
        $admin = User::factory()->adminDesa()->create();
        Kategori::factory()->create(['nama' => 'Jalan Rusak']);
        Kategori::factory()->create(['nama' => 'Fasilitas Umum']);

        $response = $this->actingAs($admin)->get('/admin/kategori');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Kategori Laporan');
        $response->assertSee('Jalan Rusak');
        $response->assertSee('Fasilitas Umum');
    }

    /**
     * PBI #30: Admin Desa dapat menambah kategori baru.
     */
    public function test_admin_dapat_menambah_kategori_baru(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->post('/admin/kategori', [
            'nama' => 'Bencana Alam & Longsor',
        ]);

        $response->assertRedirect('/admin/kategori');
        $this->assertDatabaseHas('kategori', [
            'nama' => 'Bencana Alam & Longsor',
            'aktif' => true,
        ]);
    }

    /**
     * PBI #30: Admin Desa dapat mengubah nama kategori.
     */
    public function test_admin_dapat_mengubah_nama_kategori(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put("/admin/kategori/{$kategori->id}", [
            'nama' => 'Nama Baru Diperbarui',
        ]);

        $response->assertRedirect('/admin/kategori');
        $this->assertSame('Nama Baru Diperbarui', $kategori->refresh()->nama);
    }

    /**
     * PBI #30: Admin Desa dapat menonaktifkan dan mengaktifkan kembali kategori.
     */
    public function test_admin_dapat_mengaktifkan_dan_menonaktifkan_kategori(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create(['aktif' => true]);

        // Nonaktifkan
        $this->actingAs($admin)->patch("/admin/kategori/{$kategori->id}/toggle")
            ->assertRedirect('/admin/kategori');
        $this->assertFalse($kategori->refresh()->aktif);

        // Aktifkan kembali
        $this->actingAs($admin)->patch("/admin/kategori/{$kategori->id}/toggle")
            ->assertRedirect('/admin/kategori');
        $this->assertTrue($kategori->refresh()->aktif);
    }

    /**
     * PBI #30: Kategori yang belum pernah dipakai dalam laporan dapat dihapus.
     */
    public function test_admin_dapat_menghapus_kategori_yang_belum_digunakan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Kategori Belum Dipakai']);

        $response = $this->actingAs($admin)->delete("/admin/kategori/{$kategori->id}");

        $response->assertRedirect('/admin/kategori');
        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }

    /**
     * PBI #30: Kategori yang sudah dipakai laporan warga HANYA bisa dinonaktifkan (dilarang dihapus).
     */
    public function test_admin_tidak_bisa_menghapus_kategori_yang_sudah_digunakan_pada_laporan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Kategori Terpakai']);

        // Ada laporan yang menggunakan kategori ini
        Pengaduan::factory()->create([
            'kategori_id' => $kategori->id,
            'judul' => 'Pengaduan dengan kategori terpakai',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/kategori/{$kategori->id}");

        // Ditolak dengan pesan kesalahan di session
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategori', ['id' => $kategori->id]);
    }

    /**
     * Role protection: Pengguna bukan Admin Desa dilarang mengakses manajemen kategori.
     */
    public function test_non_admin_desa_tidak_bisa_mengakses_manajemen_kategori(): void
    {
        $warga = User::factory()->warga()->create();
        $kepala = User::factory()->kepalaDesa()->create();

        $this->actingAs($warga)->get('/admin/kategori')->assertStatus(403);
        $this->actingAs($kepala)->get('/admin/kategori')->assertStatus(403);
    }
}
