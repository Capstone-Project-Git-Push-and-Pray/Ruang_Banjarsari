<?php

namespace Tests\Feature\Kepala;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPemantauanTest extends TestCase
{
    use RefreshDatabase;

    protected User $kepalaDesa;

    protected User $adminDesa;

    protected User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kepalaDesa = User::factory()->create([
            'role' => Role::KepalaDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->adminDesa = User::factory()->create([
            'role' => Role::AdminDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);

        $this->warga = User::factory()->create([
            'role' => Role::Warga,
            'status_akun' => StatusAkun::Aktif,
            'nik' => '3301010101010001',
            'nama_lengkap' => 'Warga Pelapor Teladan',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Mawar No. 12',
            'rt' => '02',
            'rw' => '03',
            'dusun' => 'Dusun Krajan',
        ]);
    }

    public function test_kepala_desa_dapat_mengakses_dashboard_pemantauan(): void
    {
        $response = $this->actingAs($this->kepalaDesa)->get(route('kepala.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Pemantauan Kepala Desa');
        $response->assertSee('Total Pengaduan');
        $response->assertSee('Status Progres Penanganan Aduan Desa');
    }

    public function test_warga_dan_admin_dilarang_mengakses_dashboard_kepala(): void
    {
        $this->actingAs($this->warga)->get(route('kepala.dashboard'))->assertForbidden();
        $this->actingAs($this->adminDesa)->get(route('kepala.dashboard'))->assertForbidden();
    }

    public function test_dashboard_kepala_desa_menampilkan_rekap_dan_5_laporan_terbaru(): void
    {
        $kategori = Kategori::factory()->create(['nama' => 'Fasilitas Umum', 'aktif' => true]);

        // Buat 6 laporan
        Pengaduan::factory()->count(6)->create([
            'user_id' => $this->warga->id,
            'kategori_id' => $kategori->id,
            'status' => StatusLaporan::Diproses,
        ]);

        $response = $this->actingAs($this->kepalaDesa)->get(route('kepala.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalLaporan', 6);
        $response->assertViewHas('countDiproses', 6);
        $response->assertViewHas('laporanTerbaru', function ($laporanTerbaru) {
            return $laporanTerbaru->count() === 5;
        });
    }

    public function test_detail_laporan_kepala_desa_menampilkan_data_pelapor_lengkap(): void
    {
        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $this->warga->id,
            'judul' => 'Jembatan Penghubung Dusun Rusak Parah',
            'status' => StatusLaporan::Diproses,
        ]);

        $response = $this->actingAs($this->kepalaDesa)->get(route('kepala.pengaduan.show', $pengaduan));

        $response->assertOk();
        // Memastikan data pelapor terlihat oleh Kepala Desa (PBI #22, PBI #36)
        $response->assertSee('Identitas Pelapor');
        $response->assertSee('WARGA PELAPOR TELADAN');
        $response->assertSee('3301010101010001');
        $response->assertSee('081234567890');
        $response->assertSee('Jl. Mawar No. 12');
        $response->assertSee('Dusun Krajan');
    }

    public function test_detail_laporan_kepala_desa_bersifat_read_only_tanpa_tombol_ubah_status(): void
    {
        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $this->warga->id,
            'judul' => 'Laporan Pemantauan Khusus Kades',
            'status' => StatusLaporan::Diajukan,
        ]);

        $response = $this->actingAs($this->kepalaDesa)->get(route('kepala.pengaduan.show', $pengaduan));

        $response->assertOk();
        // Tidak ada formulir patch status dari admin
        $response->assertDontSee(route('admin.pengaduan.status.update', $pengaduan));
        $response->assertDontSee('Simpan Perubahan Status');
        $response->assertDontSee('Ubah Status Menjadi:');
    }
}
