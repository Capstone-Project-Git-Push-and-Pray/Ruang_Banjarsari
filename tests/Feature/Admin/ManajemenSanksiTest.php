<?php

namespace Tests\Feature\Admin;

use App\Enums\FiturSanksi;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI #49: halaman manajemen sanksi Admin Desa.
 */
class ManajemenSanksiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->adminDesa()->create();
    }

    public function test_admin_melihat_sanksi_berlaku_dan_dapat_melihat_semua_riwayat(): void
    {
        $berlaku = Sanksi::factory()->create(['user_id' => User::factory()->warga()->create(['nama_lengkap' => 'Warga Berlaku'])]);
        Sanksi::factory()->berakhir()->create(['user_id' => User::factory()->warga()->create(['nama_lengkap' => 'Warga Berakhir'])]);

        $this->actingAs($this->admin);

        $this->get(route('admin.sanksi.index'))->assertOk()->assertSee('WARGA BERLAKU')->assertDontSee('WARGA BERAKHIR');
        $this->get(route('admin.sanksi.index', ['status' => 'semua']))->assertSee(['WARGA BERLAKU', 'WARGA BERAKHIR']);
        $this->get(route('admin.sanksi.index', ['q' => $berlaku->user->nik, 'fitur' => FiturSanksi::KomentarPublik->value]))
            ->assertDontSee('WARGA BERLAKU');
    }

    public function test_admin_mengubah_masa_berlaku_sanksi(): void
    {
        $sanksi = Sanksi::factory()->create();
        $berakhirBaru = now()->addDays(2)->startOfMinute();

        $this->actingAs($this->admin)->patch(route('admin.sanksi.update', $sanksi), [
            'tindakan' => 'ubah',
            'berakhir_pada' => $berakhirBaru->format('Y-m-d\TH:i'),
            'catatan_admin' => 'Dipersingkat karena warga sudah meminta maaf.',
        ])->assertSessionHas('success');

        $sanksi->refresh();
        $this->assertTrue($sanksi->berakhir_pada->equalTo($berakhirBaru));
        $this->assertFalse($sanksi->permanen);
        $this->assertSame($this->admin->id, $sanksi->diubah_oleh);
    }

    public function test_admin_menjadikan_sanksi_permanen(): void
    {
        $sanksi = Sanksi::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.sanksi.update', $sanksi), [
            'tindakan' => 'ubah',
            'permanen' => '1',
            'catatan_admin' => 'Pelanggaran berulang dan berat.',
        ])->assertSessionHas('success');

        $this->assertTrue($sanksi->fresh()->permanen);
        $this->assertNull($sanksi->fresh()->berakhir_pada);
    }

    public function test_admin_membuka_kembali_fitur_warga(): void
    {
        $sanksi = Sanksi::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.sanksi.update', $sanksi), [
            'tindakan' => Sanksi::PENCABUTAN_DIAKHIRI,
            'catatan_admin' => 'Warga sudah menunjukkan perilaku baik.',
        ])->assertSessionHas('success');

        $this->assertNull($sanksi->user->sanksiBerlaku(FiturSanksi::Aspirasi));
        $this->assertSame(Sanksi::PENCABUTAN_DIAKHIRI, $sanksi->fresh()->jenis_pencabutan);
    }

    public function test_admin_membatalkan_sanksi_yang_sudah_diakhiri(): void
    {
        $sanksi = Sanksi::factory()->create(['dicabut_pada' => now()->subDay(), 'jenis_pencabutan' => Sanksi::PENCABUTAN_DIAKHIRI]);

        $this->actingAs($this->admin)->patch(route('admin.sanksi.update', $sanksi), [
            'tindakan' => Sanksi::PENCABUTAN_DIBATALKAN,
            'catatan_admin' => 'Ternyata konten tidak melanggar.',
        ])->assertSessionHas('success');

        $this->assertSame(Sanksi::PENCABUTAN_DIBATALKAN, $sanksi->fresh()->jenis_pencabutan);
        $this->assertSame(0, $sanksi->user->sanksi()->dihitung()->count());
    }

    public function test_sanksi_dibatalkan_tidak_dapat_diubah_lagi(): void
    {
        $sanksi = Sanksi::factory()->dibatalkan()->create();

        $this->actingAs($this->admin)->patch(route('admin.sanksi.update', $sanksi), [
            'tindakan' => 'ubah',
            'permanen' => '1',
            'catatan_admin' => 'Mencoba mengaktifkan lagi.',
        ])->assertSessionHas('error');

        $this->assertFalse($sanksi->fresh()->permanen);
    }

    public function test_perubahan_sanksi_wajib_valid(): void
    {
        $sanksi = Sanksi::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.sanksi.update', $sanksi), [
            'tindakan' => 'ubah',
            'berakhir_pada' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors([
            'berakhir_pada' => 'Tanggal berakhir harus setelah waktu sekarang.',
            'catatan_admin' => 'Catatan alasan perubahan wajib diisi agar keputusan tercatat.',
        ]);

        $this->assertNull($sanksi->fresh()->diubah_oleh);
    }

    public function test_warga_dan_kepala_desa_dilarang_mengelola_sanksi(): void
    {
        $sanksi = Sanksi::factory()->create();

        foreach ([User::factory()->warga()->create(), User::factory()->kepalaDesa()->create()] as $pengguna) {
            $this->actingAs($pengguna);

            $this->get(route('admin.sanksi.index'))->assertForbidden();
            $this->patch(route('admin.sanksi.update', $sanksi), [
                'tindakan' => Sanksi::PENCABUTAN_DIAKHIRI,
                'catatan_admin' => 'Mencoba mencabut sendiri.',
            ])->assertForbidden();
        }

        $this->assertNull($sanksi->fresh()->dicabut_pada);
    }
}
