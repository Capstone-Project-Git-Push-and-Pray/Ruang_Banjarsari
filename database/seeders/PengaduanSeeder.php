<?php

namespace Database\Seeders;

use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Lampiran;
use App\Models\Pengaduan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PengaduanSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk laporan pengaduan warga mencakup seluruh tahapan alur pengaduan:
     * Diajukan -> Ditinjau -> Diproses -> Selesai / Ditolak.
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

        $kategoriPenerangan = Kategori::firstOrCreate(['nama' => 'Penerangan Jalan'], ['aktif' => true]);
        $kategoriJalan = Kategori::firstOrCreate(['nama' => 'Jalan Rusak'], ['aktif' => true]);
        $kategoriDrainase = Kategori::firstOrCreate(['nama' => 'Saluran Air dan Drainase'], ['aktif' => true]);
        $kategoriFasilitas = Kategori::firstOrCreate(['nama' => 'Fasilitas Umum'], ['aktif' => true]);
        $kategoriKetertiban = Kategori::firstOrCreate(['nama' => 'Keamanan dan Ketertiban'], ['aktif' => true]);

        // =========================================================================
        // 1. Pengaduan Tahap: Diajukan
        // =========================================================================
        $aduanDiajukan = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriPenerangan->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Lampu Penerangan Jalan Padam di Pertigaan Dusun Sukasari',
            'deskripsi' => "Lampu Penerangan Jalan Umum (PJU) pada tiang nomor 14 di pertigaan Dusun Sukasari sudah padam sejak tiga hari terakhir.\n\nKondisi jalan menjadi sangat gelap gulita saat malam hari dan rawan menimbulkan kecelakaan bagi pengendara roda dua serta pejalan kaki yang melintas.",
            'lokasi' => 'Pertigaan RT 002 / RW 003, Dusun Sukasari',
            'status' => StatusLaporan::Diajukan,
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(6),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDiajukan->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan pengaduan warga diajukan melalui SILAFA Banjarsari.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(6),
        ]);

        // =========================================================================
        // 2. Pengaduan Tahap: Ditinjau
        // =========================================================================
        $aduanDitinjau = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriJalan->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Aspal Berlubang Parah di Depan Gerbang SDN 1 Banjarsari',
            'deskripsi' => "Terdapat lubang jalan cukup lebar dan dalam kurang lebih 15 cm tepat di depan pintu gerbang SDN 1 Banjarsari.\n\nSangat membahayakan keselamatan anak-anak saat jam masuk dan pulang sekolah, terutama saat hujan karena tertutup genangan air sehingga tidak terlihat pengendara.",
            'lokasi' => 'Jl. Merdeka No. 45 (Depan Gerbang SDN 1 Banjarsari), Dusun Krajan',
            'status' => StatusLaporan::Ditinjau,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay(),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDitinjau->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan diajukan oleh warga.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDitinjau->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Laporan telah diverifikasi oleh staf pelayanan desa dan diteruskan ke Kaur Perencanaan untuk pengecekan fisik lokasi.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        if (Storage::disk(Lampiran::DISK)->exists('lampiran-pengaduan/30FsgWdBCLwbcvYYQT3zlHGEq0QI6PRo3Alua0g4.jpg')) {
            Lampiran::create([
                'pengaduan_id' => $aduanDitinjau->id,
                'path' => 'lampiran-pengaduan/30FsgWdBCLwbcvYYQT3zlHGEq0QI6PRo3Alua0g4.jpg',
                'nama_asli' => 'foto_aspal_berlubang_sdn1.jpg',
            ]);
        }

        // =========================================================================
        // 3. Pengaduan Tahap: Diproses
        // =========================================================================
        $aduanDiproses = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriDrainase->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Saluran Drainase Tersumbat Lumpur Menyebabkan Luapan Air Hujan',
            'deskripsi' => 'Saluran drainase air di sepanjang Jalan Flamboyan tertimbun endapan sedimentasi tanah dan sampah dedaunan kering, sehingga saat hujan turun aliran air meluap ke badan jalan dan pekarangan rumah warga.',
            'lokasi' => 'Jl. Flamboyan Jalur Utama RT 001 / RW 002',
            'status' => StatusLaporan::Diproses,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subHours(12),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDiproses->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan masuk sistem.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDiproses->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Berkas laporan ditinjau dan dinyatakan memenuhi kriteria tindak lanjut.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDiproses->id,
            'status' => StatusLaporan::Diproses->value,
            'catatan' => 'Tim satgas kebersihan dan pemeliharaan prasarana desa sedang dikerahkan ke lokasi untuk pengerukan lumpur saluran.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);

        if (Storage::disk(Lampiran::DISK)->exists('lampiran-pengaduan/RBbfncgp3ZH5RYe8nIZ6VxlRLTgmtyYEofbkiJFr.jpg')) {
            Lampiran::create([
                'pengaduan_id' => $aduanDiproses->id,
                'path' => 'lampiran-pengaduan/RBbfncgp3ZH5RYe8nIZ6VxlRLTgmtyYEofbkiJFr.jpg',
                'nama_asli' => 'foto_saluran_drainase_lumpur.jpg',
            ]);
        }

        // =========================================================================
        // 4. Pengaduan Tahap: Selesai
        // =========================================================================
        $aduanSelesai = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriFasilitas->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Perbaikan Tutup Gorong-Gorong Beton yang Amblas di Dekat Pos Kamling',
            'deskripsi' => 'Tutup pelat beton saluran gorong-gorong di dekat pos kamling RW 002 patah dan amblas terperosok ke bawah sehingga kendaraan roda empat tidak dapat melintas.',
            'lokasi' => 'Depan Pos Kamling RW 002 Dusun Mekarsari',
            'status' => StatusLaporan::Selesai,
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(1),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanSelesai->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan diajukan warga.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanSelesai->id,
            'status' => StatusLaporan::Ditinjau->value,
            'catatan' => 'Telah ditinjau dan dilakukan pengukuran kebutuhan cor beton pengganti.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanSelesai->id,
            'status' => StatusLaporan::Diproses->value,
            'catatan' => 'Pengecoran plat beton bertulang penutup gorong-gorong baru sedang dikerjakan tukang desa.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanSelesai->id,
            'status' => StatusLaporan::Selesai->value,
            'catatan' => 'Pemasangan penutup gorong-gorong baru telah rampung dengan tuntas. Akses jalan telah kembali normal dan aman dilalui warga.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        if (Storage::disk(Lampiran::DISK)->exists('lampiran-pengaduan/Y1v1hsi9ebQZrLoTOJsDKlCnCXqBtauRayMtBLuB.jpg')) {
            Lampiran::create([
                'pengaduan_id' => $aduanSelesai->id,
                'path' => 'lampiran-pengaduan/Y1v1hsi9ebQZrLoTOJsDKlCnCXqBtauRayMtBLuB.jpg',
                'nama_asli' => 'foto_tutup_gorong_gorong_selesai.jpg',
            ]);
        }

        // =========================================================================
        // 5. Pengaduan Tahap: Ditolak
        // =========================================================================
        $aduanDitolak = Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategoriKetertiban->id,
            'jenis' => JenisLaporan::Pengaduan,
            'judul' => 'Permintaan Penebangan Ranting Pohon di Halaman Pekarangan Rumah Pribadi',
            'deskripsi' => 'Memohon agar tim perangkat desa memotong dahan pohon mangga milik tetangga di sebelah rumah karena daunnya sering berguguran ke pekarangan rumah saya.',
            'lokasi' => 'Pekarangan Rumah Warga RT 003 / RW 001 Dusun Krajan',
            'status' => StatusLaporan::Ditolak,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(6),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDitolak->id,
            'status' => StatusLaporan::Diajukan->value,
            'catatan' => 'Laporan masuk dari warga.',
            'diubah_oleh' => $warga->id,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        RiwayatStatus::create([
            'pengaduan_id' => $aduanDitolak->id,
            'status' => StatusLaporan::Ditolak->value,
            'catatan' => 'Laporan tidak dapat diproses oleh pemerintah desa karena objek aduan berada di ranah pekarangan hak milik pribadi warga, bukan fasilitas umum desa. Disarankan untuk diselesaikan secara musyawarah kekeluargaan antar-tetangga dengan didampingi Ketua RT setempat.',
            'diubah_oleh' => $admin?->id ?? $warga->id,
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);
    }
}
