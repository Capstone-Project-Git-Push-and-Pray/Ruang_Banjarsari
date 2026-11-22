<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisLaporan;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Lampiran;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaduanManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PBI #27: Admin dapat melihat daftar laporan dengan paginasi.
     */
    public function test_admin_dapat_melihat_daftar_laporan_dengan_paginasi(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        Pengaduan::factory()->count(18)->create([
            'kategori_id' => $kategori->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/pengaduan');

        $response->assertStatus(200);
        $response->assertSee('Pengelolaan Laporan Warga');
        // Memastikan ada 15 laporan di halaman pertama (paginasi per 15)
        $this->assertCount(15, $response->viewData('pengaduans'));
    }

    /**
     * PBI #27: Admin dapat menyaring laporan berdasarkan status.
     */
    public function test_admin_dapat_memfilter_laporan_berdasarkan_status(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        $laporanDiajukan = Pengaduan::factory()->create([
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan Berstatus Diajukan',
            'status' => StatusLaporan::Diajukan,
        ]);

        $laporanDiproses = Pengaduan::factory()->create([
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan Berstatus Diproses',
            'status' => StatusLaporan::Diproses,
        ]);

        $response = $this->actingAs($admin)->get('/admin/pengaduan?status=diajukan');

        $response->assertStatus(200);
        $response->assertSee('Laporan Berstatus Diajukan');
        $response->assertDontSee('Laporan Berstatus Diproses');
    }

    /**
     * PBI #27: Admin dapat menyaring laporan berdasarkan kategori.
     */
    public function test_admin_dapat_memfilter_laporan_berdasarkan_kategori(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategoriA = Kategori::factory()->create(['nama' => 'Jalan Rusak']);
        $kategoriB = Kategori::factory()->create(['nama' => 'Penerangan Jalan']);

        Pengaduan::factory()->create([
            'kategori_id' => $kategoriA->id,
            'judul' => 'Aspal Mengelupas di Dusun 1',
        ]);

        Pengaduan::factory()->create([
            'kategori_id' => $kategoriB->id,
            'judul' => 'Lampu Bohlam Putus di Dusun 2',
        ]);

        $response = $this->actingAs($admin)->get("/admin/pengaduan?kategori_id={$kategoriA->id}");

        $response->assertStatus(200);
        $response->assertSee('Aspal Mengelupas di Dusun 1');
        $response->assertDontSee('Lampu Bohlam Putus di Dusun 2');
    }

    /**
     * PBI #27: Admin dapat menyaring laporan berdasarkan jenis (pengaduan vs aspirasi).
     */
    public function test_admin_dapat_memfilter_laporan_berdasarkan_jenis(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        Pengaduan::factory()->create([
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Sampah Menumpuk di Sungai',
        ]);

        Pengaduan::factory()->create([
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Gagasan Festival Budaya Desa',
        ]);

        $response = $this->actingAs($admin)->get('/admin/pengaduan?jenis=aspirasi');

        $response->assertStatus(200);
        $response->assertSee('Gagasan Festival Budaya Desa');
        $response->assertDontSee('Sampah Menumpuk di Sungai');
    }

    /**
     * PBI #27: Admin dapat mencari laporan berdasarkan kata kunci pencarian.
     */
    public function test_admin_dapat_mencari_laporan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pelapor = User::factory()->warga()->create([
            'nama_lengkap' => 'SUGENG PRIYANTO',
            'nik' => '3207010101900777',
        ]);
        $kategori = Kategori::factory()->create();

        Pengaduan::factory()->create([
            'user_id' => $pelapor->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Gorong-gorong Mampet di Dekat Masjid',
        ]);

        Pengaduan::factory()->create([
            'kategori_id' => $kategori->id,
            'judul' => 'Pohon Tumbang Menutupi Jalan',
        ]);

        // Cari berdasarkan nama pelapor
        $response1 = $this->actingAs($admin)->get('/admin/pengaduan?q=SUGENG');
        $response1->assertStatus(200);
        $response1->assertSee('Gorong-gorong Mampet di Dekat Masjid');
        $response1->assertDontSee('Pohon Tumbang Menutupi Jalan');

        // Cari berdasarkan judul
        $response2 = $this->actingAs($admin)->get('/admin/pengaduan?q=Tumbang');
        $response2->assertStatus(200);
        $response2->assertSee('Pohon Tumbang Menutupi Jalan');
        $response2->assertDontSee('Gorong-gorong Mampet di Dekat Masjid');
    }

    /**
     * PBI #28: Detail laporan menampilkan identitas pelapor, isi laporan, foto, dan riwayat status.
     */
    public function test_admin_dapat_melihat_detail_laporan_lengkap(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pelapor = User::factory()->warga()->create([
            'nama_lengkap' => 'SITI AMINAH',
            'nik' => '3207010101900888',
            'alamat' => 'Jl. Merdeka No 12',
            'rt' => '002',
            'rw' => '001',
            'dusun' => 'Dusun Krajan',
            'telepon' => '081234567888',
        ]);
        $kategori = Kategori::factory()->create(['nama' => 'Kebersihan dan Lingkungan']);

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $pelapor->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Timbunan Sampah Liar di Pinggir Jalan',
            'deskripsi' => 'Sampah menumpuk dan menimbulkan bau tidak sedap.',
            'lokasi' => 'Dekat Jembatan Krajan',
            'status' => StatusLaporan::Diajukan,
        ]);

        Lampiran::create([
            'pengaduan_id' => $pengaduan->id,
            'path' => 'lampiran-pengaduan/test-foto.jpg',
            'nama_asli' => 'foto_bukti_sampah.jpg',
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan baru diajukan oleh warga.',
            'diubah_oleh' => $pelapor->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);
        // Identitas Pelapor (NIK, Nama, Domisili, Kontak)
        $response->assertSee('SITI AMINAH');
        $response->assertSee('3207010101900888');
        $response->assertSee('081234567888');
        $response->assertSee('Jl. Merdeka No 12');
        $response->assertSee('Dusun Krajan');

        // Isi Laporan & Lokasi
        $response->assertSee('Timbunan Sampah Liar di Pinggir Jalan');
        $response->assertSee('Sampah menumpuk dan menimbulkan bau tidak sedap.');
        $response->assertSee('Dekat Jembatan Krajan');

        // Foto & Riwayat Status
        $response->assertSee('foto_bukti_sampah.jpg');
        $response->assertSee('Laporan baru diajukan oleh warga.');
    }

    /**
     * PBI #29: Admin dapat memperbarui status dari Diajukan -> Ditinjau.
     */
    public function test_admin_dapat_memperbarui_status_diajukan_ke_ditinjau(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pengaduan = Pengaduan::factory()->create([
            'status' => StatusLaporan::Diajukan,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Laporan telah diverifikasi oleh admin desa dan diteruskan ke kasie terkait.',
        ]);

        $response->assertRedirect("/admin/pengaduan/{$pengaduan->id}");

        $pengaduan->refresh();
        $this->assertSame(StatusLaporan::Ditinjau, $pengaduan->status);

        $this->assertDatabaseHas('riwayat_status', [
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Laporan telah diverifikasi oleh admin desa dan diteruskan ke kasie terkait.',
            'diubah_oleh' => $admin->id,
        ]);
    }

    /**
     * PBI #29: Admin dapat memperbarui status dari Ditinjau -> Diproses.
     */
    public function test_admin_dapat_memperbarui_status_ditinjau_ke_diproses(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pengaduan = Pengaduan::factory()->create([
            'status' => StatusLaporan::Ditinjau,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Diproses->value,
            'catatan' => 'Petugas kebersihan desa sedang menuju lokasi untuk pembersihan.',
        ]);

        $response->assertRedirect("/admin/pengaduan/{$pengaduan->id}");

        $pengaduan->refresh();
        $this->assertSame(StatusLaporan::Diproses, $pengaduan->status);
    }

    /**
     * PBI #29: Admin dapat memperbarui status dari Diproses -> Selesai.
     */
    public function test_admin_dapat_memperbarui_status_diproses_ke_selesai(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pengaduan = Pengaduan::factory()->create([
            'status' => StatusLaporan::Diproses,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Selesai->value,
            'catatan' => 'Pembersihan tuntas, sampah sudah diangkut ke TPA.',
        ]);

        $response->assertRedirect("/admin/pengaduan/{$pengaduan->id}");

        $pengaduan->refresh();
        $this->assertSame(StatusLaporan::Selesai, $pengaduan->status);
    }

    /**
     * PBI #29: Admin dapat menolak laporan dengan catatan wajib.
     */
    public function test_admin_dapat_menolak_laporan_dengan_catatan_wajib(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pengaduan = Pengaduan::factory()->create([
            'status' => StatusLaporan::Diajukan,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Ditolak->value,
            'catatan' => 'Laporan tidak dapat diproses karena lokasi berada di luar wilayah Desa Banjarsari.',
        ]);

        $response->assertRedirect("/admin/pengaduan/{$pengaduan->id}");

        $pengaduan->refresh();
        $this->assertSame(StatusLaporan::Ditolak, $pengaduan->status);

        $this->assertDatabaseHas('riwayat_status', [
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Ditolak->value,
            'catatan' => 'Laporan tidak dapat diproses karena lokasi berada di luar wilayah Desa Banjarsari.',
            'diubah_oleh' => $admin->id,
        ]);
    }

    /**
     * PBI #29: Menolak laporan gagal jika catatan alasan kosong.
     */
    public function test_menolak_laporan_wajib_mencantumkan_catatan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pengaduan = Pengaduan::factory()->create([
            'status' => StatusLaporan::Diajukan,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Ditolak->value,
            'catatan' => '',
        ]);

        $response->assertSessionHasErrors(['catatan']);
        $this->assertSame(StatusLaporan::Diajukan, $pengaduan->refresh()->status);
    }

    /**
     * PBI #29: Urutan status tidak valid ditolak (misal: langsung dari Diajukan ke Selesai).
     */
    public function test_urutan_status_tidak_valid_ditolak(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $pengaduan = Pengaduan::factory()->create([
            'status' => StatusLaporan::Diajukan,
        ]);

        // Mencoba melompati alur langsung ke Selesai
        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Selesai->value,
            'catatan' => 'Langsung selesai tanpa ditinjau atau diproses.',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertSame(StatusLaporan::Diajukan, $pengaduan->refresh()->status);
    }

    /**
     * Dropdown filter status pada Kelola Pengaduan hanya memuat status alur pengaduan.
     */
    public function test_dropdown_status_pada_kelola_pengaduan_hanya_memuat_alur_pengaduan(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/admin/pengaduan');

        $response->assertStatus(200);

        /** @var array<StatusLaporan> $statusList */
        $statusList = $response->viewData('statusList');

        $this->assertSame(StatusLaporan::pengaduanCases(), $statusList);
        $this->assertNotContains(StatusLaporan::Ditampung, $statusList);
        $this->assertNotContains(StatusLaporan::DalamPembahasan, $statusList);
        $this->assertNotContains(StatusLaporan::Direalisasikan, $statusList);
        $this->assertNotContains(StatusLaporan::TidakDapatDitindaklanjuti, $statusList);
    }

    /**
     * Dropdown filter status memuat alur aspirasi jika jenis aspirasi dipilih.
     */
    public function test_dropdown_status_memuat_alur_aspirasi_jika_memfilter_jenis_aspirasi(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/admin/pengaduan?jenis=aspirasi');

        $response->assertStatus(200);

        /** @var array<StatusLaporan> $statusList */
        $statusList = $response->viewData('statusList');

        $this->assertSame(StatusLaporan::aspirasiCases(), $statusList);
        $this->assertNotContains(StatusLaporan::Diproses, $statusList);
        $this->assertNotContains(StatusLaporan::Selesai, $statusList);
        $this->assertNotContains(StatusLaporan::Ditolak, $statusList);
    }
}
