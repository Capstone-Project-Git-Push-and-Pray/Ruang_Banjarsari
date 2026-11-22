<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KomentarTest extends TestCase
{
    use RefreshDatabase;

    protected User $wargaPemilik;

    protected User $wargaLain;

    protected User $adminDesa;

    protected User $kepalaDesa;

    protected Pengaduan $pengaduan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wargaPemilik = User::factory()->create([
            'role' => Role::Warga,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->wargaLain = User::factory()->create([
            'role' => Role::Warga,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->adminDesa = User::factory()->create([
            'role' => Role::AdminDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->kepalaDesa = User::factory()->create([
            'role' => Role::KepalaDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->pengaduan = Pengaduan::factory()->create([
            'user_id' => $this->wargaPemilik->id,
            'status' => StatusLaporan::Diajukan,
        ]);
    }

    public function test_warga_pemilik_dapat_menambahkan_komentar_pada_laporannya(): void
    {
        $response = $this->actingAs($this->wargaPemilik)
            ->post(route('komentar.store', $this->pengaduan), [
                'isi' => 'Mohon tindak lanjutnya segera, terima kasih.',
            ]);

        $response->assertSessionHas('status', 'komentar-berhasil-ditambahkan');

        $this->assertDatabaseHas('komentar', [
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->wargaPemilik->id,
            'isi' => 'Mohon tindak lanjutnya segera, terima kasih.',
        ]);
    }

    public function test_warga_lain_tidak_dapat_menambahkan_komentar_pada_laporan_orang_lain(): void
    {
        $response = $this->actingAs($this->wargaLain)
            ->post(route('komentar.store', $this->pengaduan), [
                'isi' => 'Komentar tidak sah oleh warga lain',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('komentar', [
            'isi' => 'Komentar tidak sah oleh warga lain',
        ]);
    }

    public function test_admin_desa_dapat_membalas_komentar_pada_laporan_warga(): void
    {
        $response = $this->actingAs($this->adminDesa)
            ->post(route('komentar.store', $this->pengaduan), [
                'isi' => 'Baik, tim petugas lapangan kami sedang memeriksa lokasi.',
            ]);

        $response->assertSessionHas('status', 'komentar-berhasil-ditambahkan');

        $this->assertDatabaseHas('komentar', [
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->adminDesa->id,
            'isi' => 'Baik, tim petugas lapangan kami sedang memeriksa lokasi.',
        ]);
    }

    public function test_komentar_tampil_berurutan_secara_kronologis(): void
    {
        $komentar1 = Komentar::factory()->create([
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->wargaPemilik->id,
            'isi' => 'Komentar pertama warga',
            'created_at' => now()->subMinutes(10),
        ]);

        $komentar2 = Komentar::factory()->create([
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->adminDesa->id,
            'isi' => 'Balasan pertama Admin Desa',
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->wargaPemilik)
            ->get(route('warga.pengaduan.show', $this->pengaduan));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Komentar pertama warga',
            'Balasan pertama Admin Desa',
        ]);
        $response->assertSee('Admin Desa');
    }

    public function test_komentar_tidak_dapat_diubah_oleh_orang_lain(): void
    {
        $komentar = Komentar::factory()->create([
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->wargaPemilik->id,
            'isi' => 'Teks asli dari warga',
        ]);

        // Warga lain mencoba mengedit komentar
        $response = $this->actingAs($this->wargaLain)
            ->put(route('komentar.update', $komentar), [
                'isi' => 'Percobaan sabotase komentar',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('komentar', [
            'id' => $komentar->id,
            'isi' => 'Teks asli dari warga',
        ]);
    }

    public function test_penulis_dapat_mengubah_komentarnya_sendiri(): void
    {
        $komentar = Komentar::factory()->create([
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->wargaPemilik->id,
            'isi' => 'Teks asli sebelum revisi',
        ]);

        $response = $this->actingAs($this->wargaPemilik)
            ->put(route('komentar.update', $komentar), [
                'isi' => 'Teks sudah diperbarui oleh pemilik',
            ]);

        $response->assertSessionHas('status', 'komentar-berhasil-diperbarui');

        $this->assertDatabaseHas('komentar', [
            'id' => $komentar->id,
            'isi' => 'Teks sudah diperbarui oleh pemilik',
        ]);
    }

    public function test_penulis_dapat_menghapus_komentarnya_sendiri(): void
    {
        $komentar = Komentar::factory()->create([
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->wargaPemilik->id,
            'isi' => 'Komentar yang akan dihapus',
        ]);

        $response = $this->actingAs($this->wargaPemilik)
            ->delete(route('komentar.destroy', $komentar));

        $response->assertSessionHas('status', 'komentar-berhasil-dihapus');

        $this->assertDatabaseMissing('komentar', [
            'id' => $komentar->id,
        ]);
    }

    public function test_warga_lain_tidak_dapat_menghapus_komentar_orang_lain(): void
    {
        $komentar = Komentar::factory()->create([
            'pengaduan_id' => $this->pengaduan->id,
            'user_id' => $this->wargaPemilik->id,
            'isi' => 'Komentar berharga',
        ]);

        $response = $this->actingAs($this->wargaLain)
            ->delete(route('komentar.destroy', $komentar));

        $response->assertForbidden();

        $this->assertDatabaseHas('komentar', [
            'id' => $komentar->id,
        ]);
    }

    public function test_validasi_isi_komentar(): void
    {
        $response = $this->actingAs($this->wargaPemilik)
            ->post(route('komentar.store', $this->pengaduan), [
                'isi' => '',
            ]);

        $response->assertSessionHasErrors('isi');
    }
}
