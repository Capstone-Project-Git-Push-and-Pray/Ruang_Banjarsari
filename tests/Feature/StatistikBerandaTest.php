<?php

namespace Tests\Feature;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Services\StatistikLayananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatistikBerandaTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistik_dihitung_dari_data_laporan_satu_tahun_terakhir(): void
    {
        $selesai = Pengaduan::factory()->create(['status' => StatusLaporan::Selesai, 'created_at' => now()->subDays(10)]);
        Pengaduan::factory()->create(['status' => StatusLaporan::Diproses, 'created_at' => now()->subDays(5)]);
        Pengaduan::factory()->create(['status' => StatusLaporan::Diajukan, 'created_at' => now()->subDay()]);
        Pengaduan::factory()->create(['status' => StatusLaporan::Ditolak, 'created_at' => now()->subDays(3)]);
        Pengaduan::factory()->create(['status' => StatusLaporan::Selesai, 'created_at' => now()->subMonths(18)]);

        RiwayatStatus::create([
            'pengaduan_id' => $selesai->id,
            'status' => StatusLaporan::Ditinjau->value,
            'diubah_oleh' => $selesai->user_id,
        ])->forceFill(['created_at' => now()->subDays(10)->addHours(6)])->save();

        $statistik = app(StatistikLayananService::class)->ringkasan();

        $this->assertSame(4, $statistik['total']);
        $this->assertSame(25.0, $statistik['tingkat_penyelesaian']);
        $this->assertSame(75.0, $statistik['tingkat_respon']);
        $this->assertSame(['selesai' => 1, 'proses' => 1, 'menunggu' => 1, 'ditolak' => 1], $statistik['distribusi']);
        $this->assertSame(300.0, $statistik['tren_total_persen']);
        $this->assertSame(['nilai' => 6.0, 'satuan' => 'jam', 'label' => '6 Jam'], $statistik['rata_respon']);
    }

    public function test_beranda_menampilkan_statistik_asli_dan_aman_saat_belum_ada_laporan(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-counter="0"', false)
            ->assertDontSee('1,482');

        Pengaduan::factory()->count(3)->create(['status' => StatusLaporan::Selesai]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-counter="3"', false)
            ->assertSee('Selesai (100%)');
    }
}
