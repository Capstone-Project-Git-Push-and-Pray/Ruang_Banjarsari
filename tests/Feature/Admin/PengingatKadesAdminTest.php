<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use App\Models\PengingatTindakLanjut;
use App\Models\User;
use App\Services\PengingatTindakLanjutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI #52 (kotak pengingat), PBI #53 (lonceng notifikasi), dan PBI #54 (penyelesaian otomatis & jejak).
 */
class PengingatKadesAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $kepala;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->adminDesa()->create();
        $this->kepala = User::factory()->kepalaDesa()->create(['nama_lengkap' => 'Pak Kades']);
    }

    public function test_kotak_pengingat_dan_badge_tampil_di_kelola_pengaduan_hanya_saat_ada_pengingat_aktif(): void
    {
        $this->actingAs($this->admin)->get(route('admin.pengaduan.index'))
            ->assertOk()
            ->assertDontSee('Pengingat dari Kepala Desa');

        $diingatkan = $this->kirimPengingat($this->laporanTertunda(['judul' => 'Drainase tersumbat']), 'Warga sudah mengeluh.');
        $this->laporanTertunda(['judul' => 'Laporan tanpa pengingat']);

        $this->actingAs($this->admin)->get(route('admin.pengaduan.index'))
            ->assertOk()
            ->assertSee('Pengingat dari Kepala Desa (1)')
            ->assertSee('Warga sudah mengeluh.')
            ->assertSee(route('admin.pengaduan.show', $diingatkan->pengaduan_id), false)
            ->assertSee('Diingatkan Kades');
    }

    public function test_kotak_pengingat_aspirasi_terpisah_dari_pengaduan(): void
    {
        $this->kirimPengingat($this->laporanTertunda(['judul' => 'Usulan lampu taman'], aspirasi: true));

        $this->actingAs($this->admin)->get(route('admin.aspirasi.index'))
            ->assertSee('Pengingat dari Kepala Desa (1)')
            ->assertSee('Usulan lampu taman');

        $this->actingAs($this->admin)->get(route('admin.pengaduan.index'))
            ->assertDontSee('Pengingat dari Kepala Desa');
    }

    public function test_lonceng_menampilkan_jumlah_belum_dibaca_dan_membuka_laporan_saat_diklik(): void
    {
        $pengingat = $this->kirimPengingat($this->laporanTertunda(['judul' => 'Jalan amblas']));
        $notifikasi = $this->admin->notifications()->first();

        $this->assertNotNull($notifikasi);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('Notifikasi, 1 belum dibaca')
            ->assertSee('Jalan amblas');

        $this->actingAs($this->admin)
            ->get(route('admin.notifikasi.buka', $notifikasi->id))
            ->assertRedirect(route('admin.pengaduan.show', $pengingat->pengaduan_id));

        $this->assertNotNull($notifikasi->fresh()->read_at);
        $this->assertNull($pengingat->fresh()->selesai_pada, 'Menandai dibaca tidak boleh menyelesaikan pengingat.');
    }

    public function test_tandai_semua_notifikasi_sudah_dibaca_dan_notifikasi_admin_lain_tidak_bisa_dibuka(): void
    {
        $adminLain = User::factory()->adminDesa()->create();
        $this->kirimPengingat($this->laporanTertunda());
        $this->kirimPengingat($this->laporanTertunda());

        $this->actingAs($adminLain)
            ->get(route('admin.notifikasi.buka', $this->admin->notifications()->first()->id))
            ->assertNotFound();

        $this->actingAs($this->admin)->post(route('admin.notifikasi.baca-semua'))->assertRedirect();

        $this->assertSame(0, $this->admin->unreadNotifications()->count());
        $this->assertSame(2, $adminLain->unreadNotifications()->count());
    }

    public function test_pengingat_selesai_otomatis_saat_status_berubah_dari_diajukan_dan_tercatat_di_log(): void
    {
        $pengingat = $this->kirimPengingat($this->laporanTertunda(['judul' => 'Sampah liar']));

        $this->actingAs($this->kepala)->get(route('kepala.pengaduan.index'))
            ->assertDontSee('Ditindaklanjuti setelah diingatkan');

        $this->actingAs($this->admin)
            ->patch(route('admin.pengaduan.status.update', $pengingat->pengaduan_id), ['status' => StatusLaporan::Ditinjau->value])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($pengingat->fresh()->selesai_pada);
        $this->actingAs($this->admin)->get(route('admin.pengaduan.index'))
            ->assertDontSee('Pengingat dari Kepala Desa')
            ->assertDontSee('Diingatkan Kades');
        $this->actingAs($this->kepala)->get(route('kepala.pengaduan.index'))
            ->assertSee('Ditindaklanjuti setelah diingatkan');
        $this->actingAs($this->admin)->get(route('admin.log-aktivitas.index', ['jenis' => 'pengingat']))
            ->assertSee('PAK KADES mengingatkan Admin Desa tentang pengaduan &quot;Sampah liar&quot;', false);
    }

    public function test_membuka_aspirasi_yang_diingatkan_ikut_menyelesaikan_pengingat(): void
    {
        $pengingat = $this->kirimPengingat($this->laporanTertunda([], aspirasi: true));

        $this->actingAs($this->admin)->get(route('admin.aspirasi.show', $pengingat->pengaduan_id))->assertOk();

        $this->assertNotNull($pengingat->fresh()->selesai_pada);
    }

    private function kirimPengingat(Pengaduan $laporan, ?string $catatan = null): PengingatTindakLanjut
    {
        return app(PengingatTindakLanjutService::class)->kirim($laporan, $this->kepala, $catatan);
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function laporanTertunda(array $atribut = [], bool $aspirasi = false): Pengaduan
    {
        $pabrik = $aspirasi ? Pengaduan::factory()->aspirasi() : Pengaduan::factory();

        return $pabrik->create($atribut + [
            'status' => StatusLaporan::Diajukan,
            'created_at' => now()->subDays(4),
        ]);
    }
}
