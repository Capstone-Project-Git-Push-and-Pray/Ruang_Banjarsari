<?php

namespace Tests\Feature;

use App\Models\Dukungan;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI #43: jumlah dukungan dan komentar yang masuk ke aspirasi milik warga.
 */
class InfoAspirasiSayaTest extends TestCase
{
    use RefreshDatabase;

    private User $pengusul;

    private Pengaduan $aspirasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengusul = User::factory()->warga()->create();
        $this->aspirasi = Pengaduan::factory()->aspirasi()->create(['user_id' => $this->pengusul->id]);

        Dukungan::factory()->count(3)->create(['pengaduan_id' => $this->aspirasi->id]);
        Komentar::factory()->count(2)->create(['pengaduan_id' => $this->aspirasi->id]);
        // Tidak dihitung: komentar pengusul sendiri dan komentar yang disembunyikan Admin Desa
        Komentar::factory()->create(['pengaduan_id' => $this->aspirasi->id, 'user_id' => $this->pengusul->id]);
        Komentar::factory()->disembunyikan()->create(['pengaduan_id' => $this->aspirasi->id]);
    }

    public function test_daftar_laporan_saya_menampilkan_jumlah_dukungan_dan_komentar_masuk(): void
    {
        $this->actingAs($this->pengusul)->get(route('warga.pengaduan.index'))
            ->assertOk()
            ->assertSee(['3 dukungan', '2 komentar warga']);
    }

    public function test_detail_aspirasi_saya_menampilkan_jumlah_dukungan_dan_komentar_masuk(): void
    {
        $this->actingAs($this->pengusul)->get(route('warga.pengaduan.show', $this->aspirasi))
            ->assertOk()
            ->assertSeeInOrder(['3', 'warga mendukung', '2', 'komentar dari warga lain dan aparatur']);
    }

    public function test_pengaduan_tidak_menampilkan_info_dukungan(): void
    {
        $pengaduan = Pengaduan::factory()->create(['user_id' => $this->pengusul->id]);

        $this->actingAs($this->pengusul)->get(route('warga.pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertDontSee('warga mendukung');
    }
}
