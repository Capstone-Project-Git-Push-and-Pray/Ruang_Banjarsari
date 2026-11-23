<?php

namespace Tests\Feature;

use App\Models\Dukungan;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI #41: dukungan warga pada aspirasi.
 */
class DukunganAspirasiTest extends TestCase
{
    use RefreshDatabase;

    private User $warga;

    private Pengaduan $aspirasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warga = User::factory()->warga()->create();
        $this->aspirasi = Pengaduan::factory()->aspirasi()->create();
    }

    public function test_warga_dapat_mendukung_aspirasi_warga_lain(): void
    {
        $this->actingAs($this->warga)
            ->from(route('warga.aspirasi.index'))
            ->post(route('warga.aspirasi.dukungan.store', $this->aspirasi))
            ->assertRedirect(route('warga.aspirasi.index'))
            ->assertSessionMissing('success');

        $this->assertDatabaseHas('dukungan', ['pengaduan_id' => $this->aspirasi->id, 'user_id' => $this->warga->id]);

        $this->get(route('warga.aspirasi.show', $this->aspirasi))->assertSeeInOrder(['Didukung', '1 warga mendukung']);
        $this->get(route('warga.aspirasi.index'))->assertSeeInOrder(['Didukung', '1 warga mendukung']);
    }

    public function test_dukungan_ganda_dari_warga_yang_sama_hanya_tercatat_sekali(): void
    {
        $this->actingAs($this->warga);

        $this->postJson(route('warga.aspirasi.dukungan.store', $this->aspirasi))->assertExactJson(['didukung' => true, 'jumlah' => 1]);
        $this->postJson(route('warga.aspirasi.dukungan.store', $this->aspirasi))->assertExactJson(['didukung' => true, 'jumlah' => 1]);

        $this->assertDatabaseCount('dukungan', 1);
    }

    public function test_warga_hanya_membatalkan_dukungannya_sendiri(): void
    {
        Dukungan::factory()->create(['pengaduan_id' => $this->aspirasi->id, 'user_id' => $this->warga->id]);
        $dukunganLain = Dukungan::factory()->create(['pengaduan_id' => $this->aspirasi->id]);

        $this->actingAs($this->warga)
            ->deleteJson(route('warga.aspirasi.dukungan.destroy', $this->aspirasi))
            ->assertExactJson(['didukung' => false, 'jumlah' => 1]);

        $this->assertSame([$dukunganLain->id], Dukungan::pluck('id')->all());
    }

    public function test_pengusul_dilarang_mendukung_aspirasinya_sendiri(): void
    {
        $this->actingAs($this->aspirasi->user)
            ->post(route('warga.aspirasi.dukungan.store', $this->aspirasi))
            ->assertForbidden();

        $this->assertDatabaseEmpty('dukungan');
    }

    public function test_pengaduan_dan_aspirasi_tersembunyi_tidak_dapat_didukung(): void
    {
        $this->actingAs($this->warga);

        foreach ([Pengaduan::factory()->create(), Pengaduan::factory()->aspirasi()->disembunyikan()->create()] as $laporan) {
            $this->post(route('warga.aspirasi.dukungan.store', $laporan))->assertNotFound();
        }

        $this->assertDatabaseEmpty('dukungan');
    }

    public function test_perangkat_desa_dan_warga_belum_aktif_tidak_dapat_mendukung(): void
    {
        $this->actingAs(User::factory()->adminDesa()->create())
            ->post(route('warga.aspirasi.dukungan.store', $this->aspirasi))->assertForbidden();
        $this->actingAs(User::factory()->kepalaDesa()->create())
            ->post(route('warga.aspirasi.dukungan.store', $this->aspirasi))->assertForbidden();
        $this->actingAs(User::factory()->warga()->menungguTinjauan()->create())
            ->post(route('warga.aspirasi.dukungan.store', $this->aspirasi))->assertRedirect(route('pendaftaran.status'));

        $this->assertDatabaseEmpty('dukungan');
    }

    public function test_admin_melihat_dan_mengurutkan_aspirasi_berdasarkan_dukungan(): void
    {
        $this->aspirasi->update(['judul' => 'Aspirasi Paling Dibutuhkan']);
        $this->aspirasi->forceFill(['created_at' => now()->subDay()])->save();
        Pengaduan::factory()->aspirasi()->create(['judul' => 'Aspirasi Terbaru']);
        Dukungan::factory()->count(2)->create(['pengaduan_id' => $this->aspirasi->id]);

        $this->actingAs(User::factory()->adminDesa()->create())
            ->get(route('admin.aspirasi.index', ['urut' => 'dukungan']))
            ->assertSeeInOrder(['Aspirasi Paling Dibutuhkan', '2 dukungan', 'Aspirasi Terbaru', '0 dukungan']);
    }
}
