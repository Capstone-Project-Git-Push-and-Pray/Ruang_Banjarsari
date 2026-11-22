<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisLaporan;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Database\Seeders\AspirasiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AspirasiManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin dapat melihat daftar aspirasi dan hanya menampilkan laporan dengan jenis aspirasi.
     */
    public function test_admin_dapat_melihat_daftar_aspirasi_dan_hanya_menampilkan_jenis_aspirasi(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $warga = User::factory()->warga()->create();
        $kategori = Kategori::factory()->create();

        // Buat 1 Pengaduan biasa (keluhan kerusakan)
        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Jalan Rusak Berlubang Parah',
        ]);

        // Buat 2 Aspirasi warga (usulan gagasan)
        $aspirasi1 = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Usulan Pembangunan Taman Baca Anak',
        ]);

        $aspirasi2 = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Inisiatif Bank Sampah Mandiri',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.aspirasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Aspirasi Warga');
        $response->assertSee($aspirasi1->judul);
        $response->assertSee($aspirasi2->judul);
        $response->assertDontSee($pengaduan->judul);
    }

    /**
     * Warga dan Kepala Desa dilarang mengakses halaman kelola aspirasi admin.
     */
    public function test_warga_dan_kepala_desa_tidak_dapat_mengakses_halaman_admin_aspirasi(): void
    {
        $warga = User::factory()->warga()->create();
        $kepala = User::factory()->kepalaDesa()->create();

        $this->actingAs($warga)->get(route('admin.aspirasi.index'))->assertStatus(403);
        $this->actingAs($kepala)->get(route('admin.aspirasi.index'))->assertStatus(403);
    }

    /**
     * Admin dapat memfilter aspirasi berdasarkan status dan kategori.
     */
    public function test_admin_dapat_memfilter_aspirasi_berdasarkan_status_dan_kategori(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $kategoriA = Kategori::factory()->create(['nama' => 'Pendidikan']);
        $kategoriB = Kategori::factory()->create(['nama' => 'Kesehatan']);

        $aspirasiDitinjau = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'kategori_id' => $kategoriA->id,
            'judul' => 'Aspirasi Ditinjau Pendidikan',
            'status' => StatusLaporan::Ditinjau,
        ]);

        $aspirasiDiproses = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'kategori_id' => $kategoriB->id,
            'judul' => 'Aspirasi Diproses Kesehatan',
            'status' => StatusLaporan::Diproses,
        ]);

        // Filter status=ditinjau
        $resStatus = $this->actingAs($admin)->get(route('admin.aspirasi.index', ['status' => 'ditinjau']));
        $resStatus->assertStatus(200);
        $resStatus->assertSee($aspirasiDitinjau->judul);
        $resStatus->assertDontSee($aspirasiDiproses->judul);

        // Filter kategori_id=$kategoriB->id
        $resKat = $this->actingAs($admin)->get(route('admin.aspirasi.index', ['kategori_id' => $kategoriB->id]));
        $resKat->assertStatus(200);
        $resKat->assertSee($aspirasiDiproses->judul);
        $resKat->assertDontSee($aspirasiDitinjau->judul);
    }

    /**
     * Admin dapat mencari aspirasi berdasarkan judul.
     */
    public function test_admin_dapat_mencari_aspirasi_berdasarkan_kata_kunci(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $aspirasi1 = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Pemberdayaan UMKM Desa',
        ]);

        $aspirasi2 = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Penanaman Pohon Lindung',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.aspirasi.index', ['q' => 'UMKM']));
        $response->assertStatus(200);
        $response->assertSee($aspirasi1->judul);
        $response->assertDontSee($aspirasi2->judul);
    }

    /**
     * Admin dapat melihat detail aspirasi, dan aspirasi yang berstatus diajukan otomatis berubah menjadi ditinjau.
     */
    public function test_admin_dapat_melihat_detail_aspirasi_dan_status_diajukan_otomatis_menjadi_ditinjau(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $warga = User::factory()->warga()->create(['nama_lengkap' => 'Budi Santoso']);

        $aspirasi = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::Diajukan,
            'judul' => 'Usulan Wifi Publik di Balai Warga',
            'deskripsi' => 'Pengadaan hotspot wifi gratis untuk anak sekolah.',
        ]);

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Lampu Jalan Padam',
        ]);

        // Melihat detail aspirasi valid
        $response = $this->actingAs($admin)->get(route('admin.aspirasi.show', $aspirasi));
        $response->assertStatus(200);
        $response->assertSee('Kembali ke Daftar Aspirasi');
        $response->assertSee($aspirasi->judul);
        $response->assertSee($warga->nama_lengkap);

        // Status otomatis berubah menjadi Ditinjau
        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::Ditinjau, $aspirasi->status);
        $this->assertDatabaseHas('riwayat_status', [
            'pengaduan_id' => $aspirasi->id,
            'status' => StatusLaporan::Ditinjau->value,
            'diubah_oleh' => $admin->id,
        ]);

        // Mengakses pengaduan di route aspirasi harus 404
        $resPengaduan = $this->actingAs($admin)->get(route('admin.aspirasi.show', $pengaduan));
        $resPengaduan->assertStatus(404);
    }

    /**
     * Seeder aspirasi dapat menghasilkan minimal 2 contoh aspirasi realistis.
     */
    public function test_seeder_aspirasi_menghasilkan_dua_contoh_aspirasi_realistis(): void
    {
        $admin = User::factory()->adminDesa()->create();
        User::factory()->warga()->create();

        $this->seed(AspirasiSeeder::class);

        $this->assertDatabaseCount('pengaduan', 6);
        $this->assertDatabaseHas('pengaduan', [
            'jenis' => JenisLaporan::Aspirasi->value,
            'judul' => 'Usulan Pembangunan Taman Baca & Ruang Terbuka Hijau Dusun Sukasari',
            'status' => StatusLaporan::Ditampung->value,
        ]);
        $this->assertDatabaseHas('pengaduan', [
            'jenis' => JenisLaporan::Aspirasi->value,
            'judul' => 'Gagasan Pembentukan Bank Sampah & Pengomposan Organik Mandiri Dusun Krajan',
            'status' => StatusLaporan::DalamPembahasan->value,
        ]);
    }

    /**
     * Alur pemrosesan aspirasi berjalan sesuai workflow resmi:
     * 1. Diajukan -> 2. Ditinjau -> 3. Ditampung -> 4A. Dalam Pembahasan -> Direalisasikan.
     */
    public function test_alur_pemrosesan_aspirasi_mengikuti_tahapan_workflow_resmi(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $aspirasi = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::Diajukan,
        ]);

        // 1. Diajukan -> 2. Ditinjau
        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Aspirasi diperiksa staf desa.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasi));

        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::Ditinjau, $aspirasi->status);
        $this->assertEquals([
            StatusLaporan::Ditampung,
            StatusLaporan::TidakDapatDitindaklanjuti,
        ], $aspirasi->allowedNextStatuses());

        // 2. Ditinjau -> 3. Ditampung
        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::Ditampung->value,
            'catatan' => 'Aspirasi ditampung sebagai masukan pemerintah desa.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasi));

        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::Ditampung, $aspirasi->status);
        $this->assertEquals([
            StatusLaporan::DalamPembahasan,
            StatusLaporan::Direalisasikan,
            StatusLaporan::TidakDapatDitindaklanjuti,
        ], $aspirasi->allowedNextStatuses());

        // 3. Ditampung -> 4A. Dalam Pembahasan
        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::DalamPembahasan->value,
            'catatan' => 'Sedang dibahas dalam forum Musrenbangdes.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasi));

        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::DalamPembahasan, $aspirasi->status);
        $this->assertEquals([
            StatusLaporan::Direalisasikan,
            StatusLaporan::TidakDapatDitindaklanjuti,
        ], $aspirasi->allowedNextStatuses());

        // 4. Dalam Pembahasan -> Direalisasikan (Selesai)
        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::Direalisasikan->value,
            'catatan' => 'Pembangunan taman baca resmi diwujudkan dan dianggarkan.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasi));

        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::Direalisasikan, $aspirasi->status);
        $this->assertEmpty($aspirasi->allowedNextStatuses());
    }

    /**
     * Dari status Ditampung, admin dapat langsung memilih 4B. Direalisasikan.
     */
    public function test_aspirasi_ditampung_dapat_langsung_direalisasikan_cabang_4b(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $aspirasi = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::Ditampung,
        ]);

        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::Direalisasikan->value,
            'catatan' => 'Aspirasi langsung diwujudkan karena dapat segera dilaksanakan oleh desa.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasi));

        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::Direalisasikan, $aspirasi->status);
    }

    /**
     * Mengubah aspirasi menjadi Tidak Dapat Ditindaklanjuti wajib menyertakan catatan penjelasan.
     */
    public function test_aspirasi_tidak_dapat_ditindaklanjuti_wajib_menyertakan_catatan(): void
    {
        $admin = User::factory()->adminDesa()->create();
        $aspirasi = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::DalamPembahasan,
        ]);

        // Tanpa catatan -> harus error validasi
        $response = $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::TidakDapatDitindaklanjuti->value,
            'catatan' => '',
        ]);

        $response->assertSessionHasErrors('catatan');
        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::DalamPembahasan, $aspirasi->status);

        // Dengan catatan -> berhasil
        $responseSuccess = $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasi), [
            'status' => StatusLaporan::TidakDapatDitindaklanjuti->value,
            'catatan' => 'Usulan berada di luar kewenangan desa dan memerlukan koordinasi tingkat provinsi.',
        ]);

        $responseSuccess->assertRedirect(route('admin.aspirasi.show', $aspirasi));
        $aspirasi->refresh();
        $this->assertSame(StatusLaporan::TidakDapatDitindaklanjuti, $aspirasi->status);
    }

    /**
     * Admin dapat menolak aspirasi pada setiap tahapan (Ditinjau dan Ditampung).
     */
    public function test_admin_dapat_menolak_aspirasi_pada_tahap_ditinjau_dan_ditampung(): void
    {
        $admin = User::factory()->adminDesa()->create();

        // 1. Tolak dari tahap Ditinjau
        $aspirasiDitinjau = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::Ditinjau,
        ]);

        $this->assertTrue($aspirasiDitinjau->canTransitionTo(StatusLaporan::TidakDapatDitindaklanjuti));
        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasiDitinjau), [
            'status' => StatusLaporan::TidakDapatDitindaklanjuti->value,
            'catatan' => 'Usulan ditolak pada tahap peninjauan awal.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasiDitinjau));

        $aspirasiDitinjau->refresh();
        $this->assertSame(StatusLaporan::TidakDapatDitindaklanjuti, $aspirasiDitinjau->status);

        // 2. Tolak dari tahap Ditampung
        $aspirasiDitampung = Pengaduan::factory()->create([
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::Ditampung,
        ]);

        $this->assertTrue($aspirasiDitampung->canTransitionTo(StatusLaporan::TidakDapatDitindaklanjuti));
        $this->actingAs($admin)->patch(route('admin.pengaduan.status.update', $aspirasiDitampung), [
            'status' => StatusLaporan::TidakDapatDitindaklanjuti->value,
            'catatan' => 'Usulan ditolak setelah ditampung karena keterbatasan anggaran.',
        ])->assertRedirect(route('admin.aspirasi.show', $aspirasiDitampung));

        $aspirasiDitampung->refresh();
        $this->assertSame(StatusLaporan::TidakDapatDitindaklanjuti, $aspirasiDitampung->status);
    }
}
