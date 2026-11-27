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
 * PBI #52 (kotak pengingat di halaman Kelola Pengaduan dan Kelola Aspirasi).
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
