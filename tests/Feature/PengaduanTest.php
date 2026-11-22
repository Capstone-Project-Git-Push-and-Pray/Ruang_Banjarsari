<?php

namespace Tests\Feature;

use App\Enums\JenisLaporan;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Lampiran;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaduanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PBI #24: Warga berstatus aktif dapat membuka formulir pembuatan pengaduan.
     */
    public function test_warga_aktif_dapat_melihat_form_pengaduan(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create(['nama' => 'Infrastruktur Jalan', 'aktif' => true]);

        $response = $this->actingAs($warga)->get('/warga/pengaduan/buat');

        $response->assertStatus(200);
        $response->assertSee('Buat Laporan Baru');
        $response->assertSee('Infrastruktur Jalan');
        $response->assertSee('Pengaduan');
        $response->assertSee('Aspirasi');
    }

    /**
     * PBI #24: Warga yang belum berstatus aktif tidak dapat membuka formulir pengaduan.
     */
    public function test_warga_belum_aktif_tidak_bisa_mengakses_form_pengaduan(): void
    {
        $wargaPending = User::factory()->warga()->menungguTinjauan()->create();
        $this->actingAs($wargaPending)
            ->get('/warga/pengaduan/buat')
            ->assertRedirect('/pendaftaran/status');

        $wargaDitolak = User::factory()->warga()->ditolak()->create();
        $this->actingAs($wargaDitolak)
            ->get('/warga/pengaduan/buat')
            ->assertRedirect('/pendaftaran/status');
    }

    /**
     * PBI #24: Warga aktif dapat mengajukan pengaduan valid (tersimpan diajukan, tanpa anonim).
     */
    public function test_warga_aktif_dapat_mengajukan_pengaduan_valid(): void
    {
        $warga = User::factory()->warga()->create([
            'status_akun' => StatusAkun::Aktif,
            'nama_lengkap' => 'TEGUH PRAKOSO',
            'nik' => '3207010101900055',
        ]);
        $kategori = Kategori::factory()->create(['nama' => 'Fasilitas Umum']);

        $payload = [
            'jenis' => JenisLaporan::Pengaduan->value,
            'kategori_id' => $kategori->id,
            'judul' => 'Tiang Listrik Miring di Dekat Pos Ronda',
            'deskripsi' => 'Tiang listrik di perempatan RT 02 Dusun Krajan tampak miring dan rawan roboh saat hujan lebat.',
            'lokasi' => 'Perempatan RT 02 Dusun Krajan',
        ];

        $response = $this->actingAs($warga)->post('/warga/pengaduan', $payload);

        $pengaduan = Pengaduan::where('judul', 'Tiang Listrik Miring di Dekat Pos Ronda')->first();
        $this->assertNotNull($pengaduan);

        $response->assertRedirect("/warga/pengaduan/{$pengaduan->id}");

        // Kriteria PBI #24: tersimpan berstatus Diajukan, identitas pelapor dan waktu tercatat, tidak anonim
        $this->assertSame($warga->id, $pengaduan->user_id);
        $this->assertSame(StatusLaporan::Diajukan, $pengaduan->status);
        $this->assertSame(JenisLaporan::Pengaduan, $pengaduan->jenis);
        $this->assertSame('Perempatan RT 02 Dusun Krajan', $pengaduan->lokasi);
        $this->assertNotNull($pengaduan->created_at);

        // Riwayat status awal tercatat
        $this->assertDatabaseHas('riwayat_status', [
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Diajukan->value,
            'diubah_oleh' => $warga->id,
        ]);
    }

    /**
     * PBI #24: Warga aktif dapat mengajukan aspirasi.
     */
    public function test_warga_aktif_dapat_mengajukan_aspirasi_valid(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        $payload = [
            'jenis' => JenisLaporan::Aspirasi->value,
            'kategori_id' => $kategori->id,
            'judul' => 'Usulan Pembangunan Taman Baca Anak',
            'deskripsi' => 'Mohon dipertimbangkan pengadaan pojok baca desa di area balai desa untuk anak-anak.',
            'lokasi' => 'Kompleks Balai Desa Banjarsari',
        ];

        $response = $this->actingAs($warga)->post('/warga/pengaduan', $payload);

        $pengaduan = Pengaduan::where('judul', 'Usulan Pembangunan Taman Baca Anak')->first();
        $this->assertNotNull($pengaduan);

        $response->assertRedirect("/warga/pengaduan/{$pengaduan->id}");
        $this->assertSame(JenisLaporan::Aspirasi, $pengaduan->jenis);
        $this->assertSame(StatusLaporan::Diajukan, $pengaduan->status);
    }

    /**
     * PBI #24: Validasi kelengkapan form pengaduan.
     */
    public function test_pengajuan_laporan_memerlukan_data_wajib(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);

        $response = $this->actingAs($warga)->post('/warga/pengaduan', [
            'jenis' => '',
            'kategori_id' => '',
            'judul' => '',
            'deskripsi' => '',
        ]);

        $response->assertSessionHasErrors(['jenis', 'kategori_id', 'judul', 'deskripsi']);
    }

    /**
     * PBI #25: Warga dapat mengunggah hingga 3 foto JPG/PNG (maksimal 2 MB per foto).
     */
    public function test_warga_dapat_mengunggah_foto_pendukung_jpg_png(): void
    {
        Storage::fake('public');

        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        $foto1 = UploadedFile::fake()->image('foto_jalan_1.jpg', 600, 400);
        $foto2 = UploadedFile::fake()->image('foto_jalan_2.png', 800, 600);

        $payload = [
            'jenis' => JenisLaporan::Pengaduan->value,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Rusak Berlubang Parah',
            'deskripsi' => 'Lubang jalan sedalam 15 cm sangat membahayakan pengendara motor.',
            'lokasi' => 'Jl. Desa KM 2',
            'foto' => [$foto1, $foto2],
        ];

        $response = $this->actingAs($warga)->post('/warga/pengaduan', $payload);

        $pengaduan = Pengaduan::where('judul', 'Jalan Rusak Berlubang Parah')->first();
        $this->assertNotNull($pengaduan);

        $response->assertRedirect("/warga/pengaduan/{$pengaduan->id}");

        // Verifikasi tabel lampiran
        $this->assertCount(2, $pengaduan->lampiran);
        $lampiranPertama = $pengaduan->lampiran->first();
        $this->assertSame('foto_jalan_1.jpg', $lampiranPertama->nama_asli);

        // Verifikasi fisik file tersimpan di disk public
        Storage::disk('public')->assertExists($lampiranPertama->path);

        // Verifikasi tampilan foto di halaman detail
        $showResponse = $this->actingAs($warga)->get("/warga/pengaduan/{$pengaduan->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Foto Pendukung (2 Foto)');
        $showResponse->assertSee($lampiranPertama->url);
    }

    /**
     * PBI #25: Validasi menolak berkas non-gambar atau bukan JPG/PNG.
     */
    public function test_unggah_foto_menolak_file_bukan_jpg_png(): void
    {
        Storage::fake('public');

        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        $filePdf = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $payload = [
            'jenis' => JenisLaporan::Pengaduan->value,
            'kategori_id' => $kategori->id,
            'judul' => 'Contoh Pengaduan Dokumen',
            'deskripsi' => 'Deskripsi panjang pengaduan untuk pengujian file tidak valid.',
            'foto' => [$filePdf],
        ];

        $response = $this->actingAs($warga)->post('/warga/pengaduan', $payload);

        $response->assertSessionHasErrors(['foto.0']);
    }

    /**
     * PBI #25: Validasi menolak ukuran file lebih dari 2 MB.
     */
    public function test_unggah_foto_menolak_ukuran_melebihi_2mb(): void
    {
        Storage::fake('public');

        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        // 3 MB (3000 KB > batas 2048 KB)
        $fotoBesar = UploadedFile::fake()->create('foto_raksasa.jpg', 3000, 'image/jpeg');

        $payload = [
            'jenis' => JenisLaporan::Pengaduan->value,
            'kategori_id' => $kategori->id,
            'judul' => 'Contoh Pengaduan Ukuran',
            'deskripsi' => 'Deskripsi panjang pengaduan untuk pengujian ukuran berkas.',
            'foto' => [$fotoBesar],
        ];

        $response = $this->actingAs($warga)->post('/warga/pengaduan', $payload);

        $response->assertSessionHasErrors(['foto.0']);
    }

    /**
     * PBI #25: Validasi menolak jika lebih dari 3 foto dilampirkan.
     */
    public function test_unggah_foto_menolak_lebih_dari_3_foto(): void
    {
        Storage::fake('public');

        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        $fotos = [
            UploadedFile::fake()->image('foto1.jpg'),
            UploadedFile::fake()->image('foto2.jpg'),
            UploadedFile::fake()->image('foto3.jpg'),
            UploadedFile::fake()->image('foto4.jpg'),
        ];

        $payload = [
            'jenis' => JenisLaporan::Pengaduan->value,
            'kategori_id' => $kategori->id,
            'judul' => 'Contoh Pengaduan Banyak Foto',
            'deskripsi' => 'Deskripsi panjang pengaduan untuk pengujian batas jumlah foto.',
            'foto' => $fotos,
        ];

        $response = $this->actingAs($warga)->post('/warga/pengaduan', $payload);

        $response->assertSessionHasErrors(['foto']);
    }

    /**
     * PBI #26: Warga hanya melihat daftar laporan miliknya sendiri, terurut terbaru, dan menampilkan status.
     */
    public function test_warga_hanya_melihat_daftar_laporannya_sendiri_terurut_terbaru(): void
    {
        $wargaA = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $wargaB = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        // Laporan pertama warga A (dibuat lebih awal)
        $laporan1 = Pengaduan::factory()->create([
            'user_id' => $wargaA->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan A Pertama',
            'status' => StatusLaporan::Diajukan,
            'created_at' => now()->subDays(2),
        ]);

        // Laporan kedua warga A (dibuat lebih baru)
        $laporan2 = Pengaduan::factory()->create([
            'user_id' => $wargaA->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan A Kedua Terbaru',
            'status' => StatusLaporan::Diproses,
            'created_at' => now()->subDay(),
        ]);

        // Laporan warga B
        $laporanB = Pengaduan::factory()->create([
            'user_id' => $wargaB->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan Rahasia Warga B',
            'status' => StatusLaporan::Diajukan,
        ]);

        $response = $this->actingAs($wargaA)->get('/warga/pengaduan');

        $response->assertStatus(200);

        // Hanya laporan milik Warga A yang tampil
        $response->assertSee('Laporan A Pertama');
        $response->assertSee('Laporan A Kedua Terbaru');
        $response->assertDontSee('Laporan Rahasia Warga B');

        // Status laporan tampil di daftar
        $response->assertSee('Diajukan');
        $response->assertSee('Diproses');

        // Terurut terbaru (Laporan 2 muncul sebelum Laporan 1)
        $content = $response->getContent();
        $posisiLaporan2 = strpos($content, 'Laporan A Kedua Terbaru');
        $posisiLaporan1 = strpos($content, 'Laporan A Pertama');
        $this->assertTrue($posisiLaporan2 < $posisiLaporan1, 'Laporan tidak terurut dari yang terbaru');
    }

    /**
     * PBI #26: Warga dapat melihat detail lengkap laporan miliknya.
     */
    public function test_warga_dapat_melihat_detail_laporannya(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create(['nama' => 'Penerangan Jalan']);

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Lampu PJU Padam di Dusun Banjarsari Barat',
            'deskripsi' => 'Sudah tiga malam lampu penerangan jalan mati total sehingga rawan kecelakaan.',
            'lokasi' => 'Depan Masjid Baiturrahman RT 04',
            'status' => StatusLaporan::Ditinjau,
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);
        $response->assertSee('Lampu PJU Padam di Dusun Banjarsari Barat');
        $response->assertSee('Sudah tiga malam lampu penerangan jalan mati total');
        $response->assertSee('Depan Masjid Baiturrahman RT 04');
        $response->assertSee('Penerangan Jalan');
        $response->assertSee('Ditinjau');
    }

    /**
     * PBI #26: Halaman riwayat menampilkan ringkasan metrik dan tahapan proses khusus Pengaduan vs Aspirasi.
     */
    public function test_halaman_riwayat_menampilkan_metrik_dan_tahapan_proses_berbeda(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        // 1. Pengaduan sedang berjalan (Ditinjau)
        Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Pengaduan Jalan Rusak',
            'status' => StatusLaporan::Ditinjau,
        ]);

        // 2. Aspirasi sedang berjalan (Ditampung)
        Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Aspirasi Taman Baca',
            'status' => StatusLaporan::Ditampung,
        ]);

        // 3. Pengaduan selesai
        Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Pengaduan Selesai Ditangani',
            'status' => StatusLaporan::Selesai,
        ]);

        // 4. Aspirasi tidak ditindaklanjuti
        Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Aspirasi Tidak Dapat Dijalankan',
            'status' => StatusLaporan::TidakDapatDitindaklanjuti,
        ]);

        $response = $this->actingAs($warga)->get('/warga/pengaduan');

        $response->assertStatus(200);

        // Verifikasi view data ringkasan metrik
        $response->assertViewHas('totalCount', 4);
        $response->assertViewHas('sedangBerjalanCount', 2);
        $response->assertViewHas('selesaiCount', 1);
        $response->assertViewHas('tidakDilanjutkanCount', 1);

        // Verifikasi teks UI
        $response->assertSee('Semua laporan');
        $response->assertSee('Sedang berjalan');
        $response->assertSee('Selesai');
        $response->assertSee('Tidak dilanjutkan');

        // Verifikasi tahapan alur aspirasi yang khas (Ditampung, Dalam Pembahasan)
        $response->assertSee('Ditampung');
        $response->assertSee('Dalam Pembahasan');
        $response->assertSee('Tidak Ditindaklanjuti');
    }
}
