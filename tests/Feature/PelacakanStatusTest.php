<?php

namespace Tests\Feature;

use App\Enums\JenisLaporan;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Mail\StatusLaporanDiperbaruiMail;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PelacakanStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PBI #31: Warga dapat melihat status terbaru dan timeline riwayat laporan secara kronologis.
     */
    public function test_warga_dapat_melihat_status_terbaru_dan_timeline_riwayat_laporan(): void
    {
        $warga = User::factory()->warga()->create([
            'status_akun' => StatusAkun::Aktif,
            'nama_lengkap' => 'AHMAD DAHLAN',
        ]);
        $admin = User::factory()->adminDesa()->create([
            'nama_lengkap' => 'BAMBANG PETUGAS',
        ]);
        $kategori = Kategori::factory()->create(['nama' => 'Jalan Rusak']);

        // 1. Status Awal: Diajukan oleh warga
        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Berlubang di RT 03 Dusun Krajan',
            'status' => StatusLaporan::Diproses, // Status terkini
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan diajukan oleh warga.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subHours(4),
        ]);

        // 2. Ditinjau oleh admin
        RiwayatStatus::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Laporan telah diverifikasi dan disetujui untuk perbaikan.',
            'diubah_oleh' => $admin->id,
            'created_at' => now()->subHours(2),
        ]);

        // 3. Diproses oleh admin
        RiwayatStatus::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Diproses->value,
            'catatan' => 'Material aspal sudah tiba, tim pemeliharaan mulai menambal lubang.',
            'diubah_oleh' => $admin->id,
            'created_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);

        // Kriteria PBI #31: Status tampil dengan label jelas
        $response->assertSee('Status Saat Ini:');
        $response->assertSee('Timeline Penanganan Laporan');

        // Timeline kronologis menampilkan status, waktu, dan catatan
        $response->assertSee('Diajukan');
        $response->assertSee('Ditinjau');
        $response->assertSee('Diproses');
        $response->assertSee('Laporan diajukan oleh warga.');
        $response->assertSee('Laporan telah diverifikasi dan disetujui untuk perbaikan.');
        $response->assertSee('Material aspal sudah tiba, tim pemeliharaan mulai menambal lubang.');

        // Memastikan urutan kronologis dari terlama ke terbaru
        $content = $response->getContent();
        $posDiajukan = strpos($content, 'Laporan diajukan oleh warga.');
        $posDitinjau = strpos($content, 'Laporan telah diverifikasi dan disetujui untuk perbaikan.');
        $posDiproses = strpos($content, 'Material aspal sudah tiba, tim pemeliharaan mulai menambal lubang.');

        $this->assertTrue($posDiajukan < $posDitinjau, 'Urutan kronologis timeline keliru');
        $this->assertTrue($posDitinjau < $posDiproses, 'Urutan kronologis timeline keliru');
    }

    /**
     * PBI #31: Tampilan status penolakan laporan beserta catatan alasan.
     */
    public function test_warga_melihat_tampilan_penolakan_dan_alasan_jika_laporan_ditolak(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'status' => StatusLaporan::Ditolak,
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => StatusLaporan::Ditolak->value,
            'catatan' => 'Lokasi kejadian bukan merupakan wewenang jalan desa (jalan provinsi).',
            'diubah_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);
        $response->assertSee('Laporan Ditolak');
        $response->assertSee('Lokasi kejadian bukan merupakan wewenang jalan desa (jalan provinsi).');
    }

    /**
     * PBI #32: Notifikasi email dikirim ke warga saat status laporan diperbarui oleh admin.
     */
    public function test_notifikasi_email_dikirim_ke_warga_saat_status_laporan_berubah(): void
    {
        Mail::fake();

        $warga = User::factory()->warga()->create([
            'status_akun' => StatusAkun::Aktif,
            'email' => 'warga.banjarsari@example.com',
            'nama_lengkap' => 'Warga Penerima Email',
        ]);
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Perbaikan Lampu Lapangan Desa',
            'status' => StatusLaporan::Diajukan,
        ]);

        // Admin memperbarui status menjadi Ditinjau
        $response = $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Sudah kami masukkan ke agenda pemeliharaan minggu ini.',
        ]);

        $response->assertRedirect("/admin/pengaduan/{$pengaduan->id}");

        // Verifikasi email terkirim ke alamat email warga pelapor
        Mail::assertSent(StatusLaporanDiperbaruiMail::class, function (StatusLaporanDiperbaruiMail $mail) use ($pengaduan) {
            return $mail->hasTo('warga.banjarsari@example.com')
                && $mail->pengaduan->id === $pengaduan->id
                && $mail->statusLaporan === StatusLaporan::Ditinjau
                && $mail->catatan === 'Sudah kami masukkan ke agenda pemeliharaan minggu ini.';
        });
    }

    /**
     * PBI #32: Notifikasi email tidak dikirim jika warga tidak memiliki email terdaftar.
     */
    public function test_notifikasi_email_tidak_dikirim_jika_warga_tidak_memiliki_email(): void
    {
        Mail::fake();

        // Warga mendaftar hanya menggunakan nomor telepon (tanpa email)
        $wargaTanpaEmail = User::factory()->warga()->create([
            'status_akun' => StatusAkun::Aktif,
            'email' => null,
            'telepon' => '081234567899',
        ]);
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        $pengaduan = Pengaduan::factory()->create([
            'user_id' => $wargaTanpaEmail->id,
            'kategori_id' => $kategori->id,
            'status' => StatusLaporan::Diajukan,
        ]);

        $this->actingAs($admin)->patch("/admin/pengaduan/{$pengaduan->id}/status", [
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Ditinjau untuk warga tanpa email.',
        ]);

        // Memastikan tidak ada pengiriman email dan tidak menimbulkan crash/error
        Mail::assertNothingSent();
    }

    /**
     * Warga dapat melihat status dan visual stepper aspirasi yang tidak dapat ditindaklanjuti.
     */
    public function test_warga_melihat_status_dan_stepper_aspirasi_tidak_dapat_ditindaklanjuti(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        $aspirasi = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::TidakDapatDitindaklanjuti,
            'judul' => 'Usulan Pembangunan Gedung Olahraga Megah',
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasi->id,
            'status' => StatusLaporan::TidakDapatDitindaklanjuti->value,
            'catatan' => 'Anggaran desa saat ini belum mencukupi untuk proyek skala besar ini.',
            'diubah_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$aspirasi->id}");

        $response->assertStatus(200);
        $response->assertSee('Aspirasi Tidak Dapat Ditindaklanjuti');
        $response->assertSee('Ditampung');
        $response->assertSee('Dalam Pembahasan');
        $response->assertSee('Tidak Ditindaklanjuti');
        $response->assertSee('Anggaran desa saat ini belum mencukupi untuk proyek skala besar ini.');
    }

    /**
     * Warga dapat melihat status dan visual stepper aspirasi yang direalisasikan.
     */
    public function test_warga_melihat_status_dan_stepper_aspirasi_direalisasikan(): void
    {
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $admin = User::factory()->adminDesa()->create();
        $kategori = Kategori::factory()->create();

        $aspirasi = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::Direalisasikan,
            'judul' => 'Pengadaan Rak Buku Taman Baca',
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasi->id,
            'status' => StatusLaporan::Direalisasikan->value,
            'catatan' => 'Pengadaan rak buku telah selesai direalisasikan di pos ronda RT 02.',
            'diubah_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$aspirasi->id}");

        $response->assertStatus(200);
        $response->assertSee('Aspirasi Selesai Direalisasikan');
        $response->assertSee('Ditampung');
        $response->assertSee('Direalisasikan');
    }

    /**
     * Memastikan badge status laporan menggunakan warna solid bold dan teks putih.
     */
    public function test_status_badge_menggunakan_warna_solid_bold_dengan_teks_putih(): void
    {
        // 1. Verifikasi seluruh enum StatusLaporan memiliki styling teks putih dan solid
        foreach (StatusLaporan::cases() as $status) {
            $classes = $status->badgeClasses();
            $this->assertStringContainsString('text-white', $classes, "Status {$status->value} harus memiliki teks putih.");
            $this->assertMatchesRegularExpression('/bg-(amber|blue|indigo|\[#1F6B4F\]|red|zinc|sky)-[0-9]+|bg-\[#1F6B4F\]/', $classes);
        }

        // 2. Verifikasi tampilan detail laporan warga memuat badge bold dan teks putih
        $warga = User::factory()->warga()->create(['status_akun' => StatusAkun::Aktif]);
        $kategori = Kategori::factory()->create();

        $aspirasi = Pengaduan::factory()->create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'status' => StatusLaporan::TidakDapatDitindaklanjuti,
        ]);

        $response = $this->actingAs($warga)->get("/warga/pengaduan/{$aspirasi->id}");

        $response->assertStatus(200);
        $response->assertSee('Status Terkini:');
        $response->assertSee('Status Saat Ini:');
        $response->assertSee('Tidak Ditindaklanjuti');
        $response->assertSee('bg-red-600 text-white border-red-700', false);
    }
}
