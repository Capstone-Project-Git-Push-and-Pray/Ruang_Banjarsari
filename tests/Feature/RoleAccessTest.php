<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_belum_login_diarahkan_ke_login(): void
    {
        $this->get('/warga/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/kepala/dashboard')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_warga_dapat_mengakses_dashboard_warga(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get('/warga/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Warga');
    }

    public function test_warga_tidak_bisa_mengakses_rute_admin_desa(): void
    {
        $warga = User::factory()->warga()->create();

        $this->actingAs($warga)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($warga)->get('/admin/pendaftaran')->assertStatus(403);
        $this->actingAs($warga)->get('/admin/pengaduan')->assertStatus(403);
    }

    public function test_warga_tidak_bisa_mengakses_rute_kepala_desa(): void
    {
        $warga = User::factory()->warga()->create();

        $this->actingAs($warga)->get('/kepala/dashboard')->assertStatus(403);
        $this->actingAs($warga)->get('/kepala/pengaduan')->assertStatus(403);
    }

    public function test_admin_desa_bisa_mengakses_rute_admin(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin Desa');
    }

    public function test_admin_desa_tidak_bisa_mengakses_rute_kepala_desa(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $this->actingAs($admin)->get('/kepala/dashboard')->assertStatus(403);
        $this->actingAs($admin)->get('/kepala/pengaduan')->assertStatus(403);
    }

    public function test_admin_desa_tidak_bisa_mengakses_rute_warga(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $this->actingAs($admin)->get('/warga/dashboard')->assertStatus(403);
        $this->actingAs($admin)->get('/warga/pengaduan')->assertStatus(403);
        $this->actingAs($admin)->get('/warga/pengaduan/buat')->assertStatus(403);
    }

    public function test_kepala_desa_bisa_mengakses_rute_kepala_desa(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $response = $this->actingAs($kepala)->get('/kepala/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Kepala Desa');
    }

    public function test_kepala_desa_tidak_bisa_mengakses_rute_admin_desa(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $this->actingAs($kepala)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($kepala)->get('/admin/pendaftaran')->assertStatus(403);
        $this->actingAs($kepala)->get('/admin/pengaduan')->assertStatus(403);
    }

    public function test_kepala_desa_tidak_bisa_mengakses_rute_warga(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $this->actingAs($kepala)->get('/warga/dashboard')->assertStatus(403);
        $this->actingAs($kepala)->get('/warga/pengaduan')->assertStatus(403);
        $this->actingAs($kepala)->get('/warga/pengaduan/buat')->assertStatus(403);
    }

    public function test_rute_dashboard_utama_mengarahkan_sesuai_peran(): void
    {
        $warga = User::factory()->warga()->create();
        $this->actingAs($warga)->get('/dashboard')->assertRedirect('/warga/dashboard');

        $admin = User::factory()->adminDesa()->create();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin/dashboard');

        $kepala = User::factory()->kepalaDesa()->create();
        $this->actingAs($kepala)->get('/dashboard')->assertRedirect('/kepala/dashboard');
    }

    /**
     * PBI #22: Warga tidak bisa membuka laporan warga lain lewat URL.
     */
    public function test_warga_tidak_bisa_membuka_laporan_warga_lain_lewat_url(): void
    {
        $wargaA = User::factory()->warga()->create(['nama_lengkap' => 'Warga Pemilik']);
        $wargaB = User::factory()->warga()->create(['nama_lengkap' => 'Warga Pengintip']);
        $kategori = Kategori::factory()->create();

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $wargaA->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Rusak Depan Balai Desa',
        ]);

        // Warga B mencoba membuka URL pengaduan milik Warga A melalui route warga
        $responseWarga = $this->actingAs($wargaB)->get("/warga/pengaduan/{$pengaduan->id}");
        $responseWarga->assertStatus(403);

        // Warga B mencoba membuka URL rute langsung /pengaduan/{id}
        $responseDirect = $this->actingAs($wargaB)->get("/pengaduan/{$pengaduan->id}");
        $responseDirect->assertStatus(403);
    }

    /**
     * PBI #22: Warga bisa membuka laporan miliknya sendiri lewat URL.
     */
    public function test_warga_dapat_membuka_laporan_milik_sendiri_lewat_url(): void
    {
        $warga = User::factory()->warga()->create(['nama_lengkap' => 'Warga Sah']);
        $kategori = Kategori::factory()->create();

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Saluran Air Tersumbat',
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$pengaduan->id}");
        $response->assertStatus(200);
        $response->assertSee('Saluran Air Tersumbat');

        // Buka melalui route /pengaduan/{id}
        $responseDirect = $this->actingAs($warga)->get("/pengaduan/{$pengaduan->id}");
        $responseDirect->assertRedirect("/warga/pengaduan/{$pengaduan->id}");
    }

    /**
     * PBI #22: Data pelapor hanya terlihat oleh Admin Desa, Kepala Desa, dan warga pemilik.
     */
    public function test_admin_desa_dan_kepala_desa_dapat_melihat_laporan_dan_data_pelapor(): void
    {
        $pelapor = User::factory()->warga()->create([
            'nik' => '3207010101900009',
            'nama_lengkap' => 'BUDI PEMILIK LAPORAN',
            'telepon' => '081234567899',
        ]);
        $kategori = Kategori::factory()->create();
        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $pelapor->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Lampu Penerangan Jalan Padam',
        ]);

        // 1. Pemilik laporan dapat melihat data pribadinya
        $resWarga = $this->actingAs($pelapor)->get("/warga/pengaduan/{$pengaduan->id}");
        $resWarga->assertStatus(200);
        $resWarga->assertSee('3207010101900009');
        $resWarga->assertSee('BUDI PEMILIK LAPORAN');
        $resWarga->assertSee('081234567899');

        // 2. Admin Desa dapat melihat laporan warga dan data pelapornya
        $admin = User::factory()->adminDesa()->create();
        $resAdmin = $this->actingAs($admin)->get("/admin/pengaduan/{$pengaduan->id}");
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('3207010101900009');
        $resAdmin->assertSee('BUDI PEMILIK LAPORAN');
        $resAdmin->assertSee('081234567899');

        // 3. Kepala Desa dapat melihat laporan warga dan data pelapornya
        $kepala = User::factory()->kepalaDesa()->create();
        $resKepala = $this->actingAs($kepala)->get("/kepala/pengaduan/{$pengaduan->id}");
        $resKepala->assertStatus(200);
        $resKepala->assertSee('3207010101900009');
        $resKepala->assertSee('BUDI PEMILIK LAPORAN');
        $resKepala->assertSee('081234567899');
    }

    /**
     * PBI #22: Evaluasi Gate viewDataPelapor memastikan warga lain tidak diizinkan.
     */
    public function test_policy_view_data_pelapor_hanya_mengizinkan_admin_kades_dan_pemilik(): void
    {
        $wargaA = User::factory()->warga()->create();
        $wargaB = User::factory()->warga()->create();
        $admin = User::factory()->adminDesa()->create();
        $kepala = User::factory()->kepalaDesa()->create();

        $kategori = Kategori::factory()->create();
        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $wargaA->id,
            'kategori_id' => $kategori->id,
        ]);

        $this->assertTrue(Gate::forUser($wargaA)->allows('viewDataPelapor', $pengaduan));
        $this->assertFalse(Gate::forUser($wargaB)->allows('viewDataPelapor', $pengaduan));
        $this->assertTrue(Gate::forUser($admin)->allows('viewDataPelapor', $pengaduan));
        $this->assertTrue(Gate::forUser($kepala)->allows('viewDataPelapor', $pengaduan));
    }

    /**
     * PBI #23: Menu navigasi hanya menampilkan fitur yang diizinkan untuk peran Warga.
     */
    public function test_menu_navigasi_hanya_menampilkan_fitur_yang_diizinkan_peran_warga(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get('/warga/dashboard');

        $response->assertStatus(200);
        // Menu yang diizinkan untuk Warga
        $response->assertSee('Buat Laporan');
        $response->assertSee('Laporan Saya');

        // Menu peran lain yang dilarang untuk Warga
        $response->assertDontSee('Tinjau Pendaftaran');
        $response->assertDontSee('Kelola Pengaduan');
        $response->assertDontSee('Monitoring Pengaduan');
    }

    /**
     * PBI #23: Menu navigasi hanya menampilkan fitur yang diizinkan untuk peran Admin Desa.
     */
    public function test_menu_navigasi_hanya_menampilkan_fitur_yang_diizinkan_peran_admin(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        // Menu yang diizinkan untuk Admin Desa
        $response->assertSee('Tinjau Pendaftaran');
        $response->assertSee('Kelola Pengaduan');

        // Menu peran lain yang dilarang untuk Admin Desa
        $response->assertDontSee('Buat Laporan');
        $response->assertDontSee('Laporan Saya');
        $response->assertDontSee('Status Pendaftaran');
        $response->assertDontSee('Monitoring Pengaduan');
    }

    /**
     * PBI #23: Menu navigasi hanya menampilkan fitur yang diizinkan untuk peran Kepala Desa.
     */
    public function test_menu_navigasi_hanya_menampilkan_fitur_yang_diizinkan_peran_kepala_desa(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $response = $this->actingAs($kepala)->get('/kepala/dashboard');

        $response->assertStatus(200);
        // Menu yang diizinkan untuk Kepala Desa
        $response->assertSee('Monitoring Pengaduan');

        // Menu peran lain yang dilarang untuk Kepala Desa
        $response->assertDontSee('Tinjau Pendaftaran');
        $response->assertDontSee('Kelola Pengaduan');
        $response->assertDontSee('Buat Laporan');
        $response->assertDontSee('Laporan Saya');
        $response->assertDontSee('Status Pendaftaran');
    }
}
