<?php

namespace Tests\Feature;

use App\Models\Lampiran;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PBI #38: foto lampiran mengikuti hak akses laporannya.
 */
class LampiranPrivatTest extends TestCase
{
    use RefreshDatabase;

    private const PATH = 'lampiran-pengaduan/foto.jpg';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    private function buatLampiran(Pengaduan $pengaduan): Lampiran
    {
        Storage::disk('local')->put(self::PATH, 'isi-foto');

        return Lampiran::create(['pengaduan_id' => $pengaduan->id, 'path' => self::PATH, 'nama_asli' => 'foto.jpg']);
    }

    public function test_foto_pengaduan_hanya_dapat_dibuka_pemilik_dan_pihak_desa(): void
    {
        $pengaduan = Pengaduan::factory()->create();
        $lampiran = $this->buatLampiran($pengaduan);

        foreach ([$pengaduan->user, User::factory()->adminDesa()->create(), User::factory()->kepalaDesa()->create()] as $pengguna) {
            $response = $this->actingAs($pengguna)->get($lampiran->url)->assertOk();

            $this->assertSame('isi-foto', $response->streamedContent());
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }

        $this->actingAs(User::factory()->warga()->create())->get($lampiran->url)->assertNotFound();
    }

    public function test_pengunjung_tanpa_login_diarahkan_ke_halaman_masuk(): void
    {
        $lampiran = $this->buatLampiran(Pengaduan::factory()->aspirasi()->create());

        $this->get($lampiran->url)->assertRedirect(route('login'));
    }

    public function test_foto_aspirasi_publik_dapat_dibuka_warga_aktif_lain(): void
    {
        $lampiran = $this->buatLampiran(Pengaduan::factory()->aspirasi()->create());

        $this->actingAs(User::factory()->warga()->create())->get($lampiran->url)->assertOk();
        $this->actingAs(User::factory()->warga()->menungguTinjauan()->create())->get($lampiran->url)->assertNotFound();
    }

    public function test_foto_aspirasi_tersembunyi_hanya_dapat_dibuka_pengusul_dan_pihak_desa(): void
    {
        $aspirasi = Pengaduan::factory()->aspirasi()->disembunyikan()->create();
        $lampiran = $this->buatLampiran($aspirasi);

        $this->actingAs(User::factory()->warga()->create())->get($lampiran->url)->assertNotFound();
        $this->actingAs($aspirasi->user)->get($lampiran->url)->assertOk();
        $this->actingAs(User::factory()->adminDesa()->create())->get($lampiran->url)->assertOk();
    }

    public function test_foto_aspirasi_yang_dihapus_moderasi_tidak_dapat_dibuka(): void
    {
        $aspirasi = Pengaduan::factory()->aspirasi()->create();
        $lampiran = $this->buatLampiran($aspirasi);
        $aspirasi->delete();

        $this->actingAs($aspirasi->user)->get($lampiran->url)->assertNotFound();
    }

    public function test_berkas_yang_hilang_mengembalikan_404(): void
    {
        $lampiran = $this->buatLampiran(Pengaduan::factory()->create());
        Storage::disk('local')->delete(self::PATH);

        $this->actingAs(User::factory()->adminDesa()->create())->get($lampiran->url)->assertNotFound();
    }

    public function test_perintah_memindahkan_foto_lama_dan_aman_dijalankan_ulang(): void
    {
        Storage::disk('public')->put(self::PATH, 'foto-lama');
        Storage::disk('public')->put('berkas-lain.txt', 'bukan lampiran');

        $this->artisan('lampiran:pindahkan-ke-privat')->expectsOutput('1 berkas dipindahkan, 0 gagal.')->assertSuccessful();
        $this->artisan('lampiran:pindahkan-ke-privat')->expectsOutput('0 berkas dipindahkan, 0 gagal.')->assertSuccessful();

        Storage::disk('public')->assertMissing(self::PATH)->assertExists('berkas-lain.txt');
        Storage::disk('local')->assertExists(self::PATH, 'foto-lama');
    }

    public function test_perintah_tidak_menghapus_foto_publik_jika_salinan_privat_berbeda(): void
    {
        Storage::disk('public')->put(self::PATH, 'foto-lama');
        Storage::disk('local')->put(self::PATH, 'isi-berbeda');

        $this->artisan('lampiran:pindahkan-ke-privat')->assertFailed();

        Storage::disk('public')->assertExists(self::PATH, 'foto-lama');
        Storage::disk('local')->assertExists(self::PATH, 'isi-berbeda');
    }
}
