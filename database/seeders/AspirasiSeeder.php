<?php

namespace Database\Seeders;

use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class AspirasiSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk memberikan contoh aspirasi warga mencakup seluruh tahapan alur aspirasi:
     * Diajukan -> Ditinjau -> Ditampung -> Dalam Pembahasan -> Direalisasikan / Tidak Ditindaklanjuti.
     */
    public function run(): void
    {
        $warga = User::where('role', Role::Warga)
            ->where('status_akun', StatusAkun::Aktif)
            ->first();

        if (! $warga) {
            $warga = User::factory()->warga()->create([
                'nama_lengkap' => 'Budi Santoso',
                'nik' => '9999000000000001',
                'alamat' => 'Jl. Flamboyan No. 12',
                'rt' => '002',
                'rw' => '003',
                'dusun' => 'Dusun Sukasari',
            ]);
        }

        $admin = User::where('role', Role::AdminDesa)->first();

        $kategoriFasilitas = Kategori::firstOrCreate(['nama' => 'Fasilitas Umum'], ['aktif' => true]);
        $kategoriKebersihan = Kategori::firstOrCreate(['nama' => 'Kebersihan dan Lingkungan'], ['aktif' => true]);
        $kategoriKeamanan = Kategori::firstOrCreate(['nama' => 'Keamanan dan Ketertiban'], ['aktif' => true]);
        $kategoriPenerangan = Kategori::firstOrCreate(['nama' => 'Penerangan Jalan'], ['aktif' => true]);
        $kategoriLainnya = Kategori::firstOrCreate(['nama' => 'Lainnya'], ['aktif' => true]);

        // =========================================================================
        // 1. Aspirasi Tahap: Diajukan
        // =========================================================================
        $aspirasiDiajukan = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriFasilitas->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Usulan Pengadaan Wi-Fi Gratis Publik di Balai Dusun Sukasari',
            'deskripsi' => 'Mengusulkan penyediaan titik internet Wi-Fi gratis di Balai Pertemuan Dusun Sukasari untuk mendukung kegiatan belajar kelompok anak-anak sekolah, mahasiswa yang mengerjakan tugas, serta pemberdayaan literasi digital warga.',
            'lokasi' => 'Balai Dusun Sukasari, RT 002 / RW 003',
            'status' => StatusLaporan::Diajukan,
            'created_at' => now()->subHours(4),
            'updated_at' => now()->subHours(4),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDiajukan->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Usulan aspirasi dikirimkan oleh warga melalui portal layanan desa.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subHours(4),
            'updated_at' => now()->subHours(4),
        ]);

        // =========================================================================
        // 2. Aspirasi Tahap: Ditinjau
        // =========================================================================
        $aspirasiDitinjau = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriLainnya->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Gagasan Pelatihan Pemasaran Digital dan UMKM bagi Pemuda Karang Taruna',
            'deskripsi' => 'Mengusulkan program pelatihan kewirausahaan digital dan pembuatan konten promosi produk lokal bagi pemuda-pemudi desa agar dapat memasarkan produk hasil olahan pertanian warga ke marketplace daring.',
            'lokasi' => 'Aula Balai Desa Banjarsari',
            'status' => StatusLaporan::Ditinjau,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay(),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitinjau->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Aspirasi masuk ke sistem.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitinjau->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Aspirasi telah diperiksa oleh staf pelayanan desa dan diteruskan ke Seksi Kesejahteraan & Pemberdayaan Masyarakat.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // =========================================================================
        // 3. Aspirasi Tahap: Ditampung
        // =========================================================================
        $aspirasiDitampung = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriFasilitas->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Usulan Pembangunan Taman Baca & Ruang Terbuka Hijau Dusun Sukasari',
            'deskripsi' => "Mengusulkan pemanfaatan lahan kosong dekat pos ronda RT 002/RW 003 Dusun Sukasari untuk dijadikan taman baca ramah anak dan ruang terbuka hijau warga.\n\nUsulan mencakup pembuatan rak buku terbuka, kanopi peneduh, serta 3 unit bangku taman agar anak-anak memiliki area belajar bersama dan kegiatan literasi warga di waktu luang sore hari.",
            'lokasi' => 'Samping Pos Ronda RT 002/RW 003, Dusun Sukasari',
            'status' => StatusLaporan::Ditampung,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(2),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitampung->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Aspirasi diajukan melalui portal SILAFA Banjarsari.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitampung->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Aspirasi telah diperiksa dan diverifikasi oleh staf pelayanan desa.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitampung->id,
            'status' => StatusLaporan::Ditampung->value,
            'catatan' => 'Aspirasi diterima dan ditampung sebagai masukan resmi pemerintah desa untuk bahan pertimbangan pembangunan.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // =========================================================================
        // 4. Aspirasi Tahap: Dalam Pembahasan
        // =========================================================================
        $aspirasiPembahasan = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriKebersihan->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Gagasan Pembentukan Bank Sampah & Pengomposan Organik Mandiri Dusun Krajan',
            'deskripsi' => "Mengusulkan inisiatif pembentukan Bank Sampah unit Dusun Krajan yang dikelola bersama ibu-ibu PKK dan karang taruna.\n\nSelain memilah botol dan kardus bernilai ekonomis, program ini juga dapat dilengkapi wadah komposter komunal untuk mengolah sampah organik rumah tangga menjadi pupuk tanaman pekarangan warga desa.",
            'lokasi' => 'Area Balai Pertemuan RW 001, Dusun Krajan',
            'status' => StatusLaporan::DalamPembahasan,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(3),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiPembahasan->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Aspirasi gagasan lingkungan hidup diajukan oleh warga.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiPembahasan->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Aspirasi ditinjau dan diteruskan kepada Kepala Urusan Kesejahteraan Masyarakat.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiPembahasan->id,
            'status' => StatusLaporan::Ditampung->value,
            'catatan' => 'Aspirasi diterima sebagai masukan pemerintah desa.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiPembahasan->id,
            'status' => StatusLaporan::DalamPembahasan->value,
            'catatan' => 'Sedang dibahas dan dipertimbangkan lebih lanjut bersama BPD, karang taruna, dan kader PKK desa.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        // =========================================================================
        // 5. Aspirasi Tahap: Direalisasikan
        // =========================================================================
        $aspirasiRealisasi = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriPenerangan->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Usulan Pemasangan Lampu Tenaga Surya Solar Cell di Jalur Makam Desa',
            'deskripsi' => 'Mengusulkan pemasangan 4 unit tiang lampu hemat energi tenaga surya (solar cell) di sepanjang jalan setapak menuju pemakaman umum desa yang belum terjangkau jaringan listrik PLN.',
            'lokasi' => 'Jalan Setapak Pemakaman Umum Dusun Krajan',
            'status' => StatusLaporan::Direalisasikan,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(2),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiRealisasi->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Usulan diajukan melalui portal desa.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiRealisasi->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Usulan diverifikasi oleh staf pelayanan.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(12),
            'updated_at' => now()->subDays(12),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiRealisasi->id,
            'status' => StatusLaporan::Ditampung->value,
            'catatan' => 'Aspirasi masuk usulan prioritas pembangunan fasilitas publik.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiRealisasi->id,
            'status' => StatusLaporan::DalamPembahasan->value,
            'catatan' => 'Disetujui dalam Musyawarah Perencanaan Pembangunan Desa (Musrenbangdes).',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiRealisasi->id,
            'status' => StatusLaporan::Direalisasikan->value,
            'catatan' => 'Aspirasi telah diwujudkan dan selesai dipasang melalui alokasi pos prasarana Dana Desa. Lampu solar cell kini telah menerangi jalan setapak makam.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // =========================================================================
        // 6. Aspirasi Tahap: Tidak Ditindaklanjuti
        // =========================================================================
        $aspirasiDitolak = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriKeamanan->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => 'Usulan Pemasangan Portal Besi Permanen di Jalur Poros Antar-Kabupaten',
            'deskripsi' => 'Mengusulkan pembuatan portal besi permanen di perbatasan desa yang melintasi jalur antar-kabupaten guna membatasi kendaraan bermuatan berat lewat saat malam hari.',
            'lokasi' => 'Jl. Poros Provinsi Batas Perbatasan Timur',
            'status' => StatusLaporan::TidakDapatDitindaklanjuti,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(7),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitolak->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Aspirasi masuk sistem.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitolak->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Dilakukan kajian regulasi jalan dan koordinasi dengan instansi perhubungan.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aspirasiDitolak->id,
            'status' => StatusLaporan::TidakDapatDitindaklanjuti->value,
            'catatan' => 'Aspirasi tidak dapat ditindaklanjuti oleh pemerintah desa karena ruas jalan tersebut berstatus jalan poros antar-wilayah kabupaten yang menjadi wewenang Dinas Perhubungan, sehingga pemasangan portal melanggar aturan undang-undang lalu lintas jalan umum.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);
    }
}
