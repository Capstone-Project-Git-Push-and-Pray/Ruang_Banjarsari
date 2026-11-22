<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapitulasiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminDesa;

    protected User $warga;

    protected User $kepalaDesa;

    protected Kategori $kategoriInfrastruktur;

    protected Kategori $kategoriKesehatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminDesa = User::factory()->create([
            'role' => Role::AdminDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->warga = User::factory()->create([
            'role' => Role::Warga,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->kepalaDesa = User::factory()->create([
            'role' => Role::KepalaDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->kategoriInfrastruktur = Kategori::factory()->create([
            'nama' => 'Infrastruktur dan Jalan',
            'aktif' => true,
        ]);

        $this->kategoriKesehatan = Kategori::factory()->create([
            'nama' => 'Kesehatan Masyarakat',
            'aktif' => true,
        ]);
    }

    public function test_admin_desa_dapat_mengakses_halaman_rekapitulasi(): void
    {
        $response = $this->actingAs($this->adminDesa)->get(route('admin.rekapitulasi.index'));

        $response->assertOk();
        $response->assertSee('Rekapitulasi Laporan');
        $response->assertSee('Distribusi Berdasarkan Status Laporan');
        $response->assertSee('Rekapitulasi Topik');
    }

    public function test_warga_dan_kepala_desa_dilarang_mengakses_rekapitulasi_admin(): void
    {
        $this->actingAs($this->warga)->get(route('admin.rekapitulasi.index'))->assertForbidden();
        $this->actingAs($this->kepalaDesa)->get(route('admin.rekapitulasi.index'))->assertForbidden();
    }

    public function test_perhitungan_rekapitulasi_status_dan_kategori_akurat(): void
    {
        // Buat 3 laporan infrastruktur: 2 Diajukan, 1 Selesai
        Pengaduan::factory()->count(2)->create([
            'kategori_id' => $this->kategoriInfrastruktur->id,
            'status' => StatusLaporan::Diajukan,
            'jenis' => JenisLaporan::Pengaduan,
        ]);

        Pengaduan::factory()->create([
            'kategori_id' => $this->kategoriInfrastruktur->id,
            'status' => StatusLaporan::Selesai,
            'jenis' => JenisLaporan::Pengaduan,
        ]);

        // Buat 1 laporan kesehatan: Diproses
        Pengaduan::factory()->create([
            'kategori_id' => $this->kategoriKesehatan->id,
            'status' => StatusLaporan::Diproses,
            'jenis' => JenisLaporan::Aspirasi,
        ]);

        $response = $this->actingAs($this->adminDesa)->get(route('admin.rekapitulasi.index'));

        $response->assertOk();
        $response->assertViewHas('totalLaporan', 4);
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 4
                && $metrics['menunggu'] === 2
                && $metrics['diproses'] === 1
                && $metrics['selesai'] === 1
                && $metrics['ditolak'] === 0;
        });

        $response->assertViewHas('rekapStatus', function ($rekapStatus) {
            return $rekapStatus[StatusLaporan::Diajukan->value]['count'] === 2
                && $rekapStatus[StatusLaporan::Selesai->value]['count'] === 1
                && $rekapStatus[StatusLaporan::Diproses->value]['count'] === 1;
        });

        $response->assertViewHas('rekapKategori', function ($rekapKategori) {
            $infrastruktur = $rekapKategori->firstWhere('id', $this->kategoriInfrastruktur->id);
            $kesehatan = $rekapKategori->firstWhere('id', $this->kategoriKesehatan->id);

            return $infrastruktur['count'] === 3 && $kesehatan['count'] === 1;
        });
    }

    public function test_filter_rentang_tanggal_menyaring_data_laporan_dengan_akurat(): void
    {
        // Laporan 10 hari yang lalu
        Pengaduan::factory()->create([
            'kategori_id' => $this->kategoriInfrastruktur->id,
            'created_at' => now()->subDays(10),
        ]);

        // Laporan 2 hari yang lalu
        Pengaduan::factory()->create([
            'kategori_id' => $this->kategoriInfrastruktur->id,
            'created_at' => now()->subDays(2),
        ]);

        // Laporan hari ini
        Pengaduan::factory()->create([
            'kategori_id' => $this->kategoriKesehatan->id,
            'created_at' => now(),
        ]);

        // Filter 5 hari yang lalu sampai hari ini (seharusnya hanya ada 2 laporan)
        $response = $this->actingAs($this->adminDesa)->get(route('admin.rekapitulasi.index', [
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('totalLaporan', 2);
    }

    public function test_validasi_tanggal_selesai_harus_setelah_atau_sama_dengan_tanggal_mulai(): void
    {
        $response = $this->actingAs($this->adminDesa)->get(route('admin.rekapitulasi.index', [
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
        ]));

        $response->assertSessionHasErrors('tanggal_selesai');
    }
}
