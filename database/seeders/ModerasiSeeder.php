<?php

namespace Database\Seeders;

use App\Enums\AlasanModerasi;
use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\RiwayatModerasi;
use App\Models\Sanksi;
use App\Models\User;
use App\Services\ModerasiService;
use App\Services\SanksiService;
use Closure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Contoh nyata riwayat moderasi konten warga beserta sanksi otomatis yang dipicunya.
 *
 * Seluruh keputusan dijalankan melalui ModerasiService dan SanksiService, sehingga
 * tingkat dan durasi sanksi mengikuti aturan sistem (PBI #44 - #47).
 */
class ModerasiSeeder extends Seeder
{
    public function __construct(
        private ModerasiService $moderasi,
        private SanksiService $sanksi,
    ) {}

    /**
     * Isi contoh riwayat moderasi dan sanksi warga.
     */
    public function run(): void
    {
        if (RiwayatModerasi::query()->exists()) {
            $this->command?->info('Riwayat moderasi sudah berisi data, ModerasiSeeder dilewati.');

            return;
        }

        $admin = User::where('role', Role::AdminDesa)->first();
        $budi = User::where('nik', '9999000000000001')->first();
        $dewi = User::where('nik', '9999000000000004')->first();
        $eko = User::where('nik', '9999000000000005')->first();

        if (! $admin || ! $budi || ! $dewi || ! $eko) {
            $this->command?->warn('Admin desa atau warga contoh belum tersedia. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $kategoriFasilitas = Kategori::firstOrCreate(['nama' => 'Fasilitas Umum'], ['aktif' => true]);
        $kategoriLainnya = Kategori::firstOrCreate(['nama' => 'Lainnya'], ['aktif' => true]);

        // Aspirasi yang sah sebagai tempat warga berkomentar
        $aspirasiJalan = $this->pada(now()->subDays(21), fn () => $this->aspirasi(
            $budi,
            $kategoriFasilitas,
            'Usulan Perbaikan Jalan Lingkungan RT 002 Dusun Sukasari',
            'Jalan lingkungan sepanjang kurang lebih 200 meter di RT 002 berlubang dan tergenang saat hujan. Mohon dapat dimasukkan dalam rencana perbaikan melalui Dana Desa tahun depan.',
            'Jl. Flamboyan, RT 002 / RW 003',
        ));

        // ---------------------------------------------------------------
        // 1. Eko: aspirasi berisi promosi dagangan -> dihapus (Spam), tingkat 1 = peringatan
        // ---------------------------------------------------------------
        $spamPertama = $this->pada(now()->subDays(20)->setTime(9, 15), fn () => $this->aspirasi(
            $eko,
            $kategoriLainnya,
            'JUAL PUPUK ORGANIK MURAH!!! Hubungi Sekarang',
            'Dijual pupuk organik kualitas super harga grosir, bisa antar ke rumah. Minat langsung chat WA 0812-9988-7766. Stok terbatas, buruan!!!',
            'Desa Banjarsari',
        ));
        $this->pada(now()->subDays(20)->setTime(10, 2), fn () => $this->moderasi->hapus(
            $spamPertama, $admin, AlasanModerasi::Spam,
            'Konten berisi iklan dagangan pribadi, bukan aspirasi untuk desa.',
        ));

        // ---------------------------------------------------------------
        // 2. Budi: komentar menyebut nomor HP dan alamat tetangga -> dihapus (Data Pribadi)
        //    Sanksi komentar 3 hari, kini sudah berakhir.
        // ---------------------------------------------------------------
        $komentarDataPribadi = $this->pada(now()->subDays(10)->setTime(19, 40), fn () => $this->komentar(
            $aspirasiJalan,
            $budi,
            'Yang sering parkir truk di jalan ini Pak Ujang, rumahnya Jl. Flamboyan No. 7, nomor HP-nya 0813-2211-4455. Langsung tegur saja orangnya.',
        ));
        $this->pada(now()->subDays(10)->setTime(20, 5), fn () => $this->moderasi->hapus(
            $komentarDataPribadi, $admin, AlasanModerasi::DataPribadi,
            'Mencantumkan alamat dan nomor telepon warga lain tanpa izin.',
        ));

        // ---------------------------------------------------------------
        // 3. Dewi: komentar dianggap hoaks -> dihapus (Informasi Palsu), lalu sanksi DIBATALKAN
        // ---------------------------------------------------------------
        $komentarDikira = $this->pada(now()->subDays(15)->setTime(8, 30), fn () => $this->komentar(
            $aspirasiJalan,
            $dewi,
            'Info dari rapat RW kemarin, anggaran perbaikan jalan ini sudah dialokasikan Rp 85 juta di APBDes tahun depan.',
        ));
        $riwayatDikira = $this->pada(now()->subDays(15)->setTime(9, 0), fn () => $this->moderasi->hapus(
            $komentarDikira, $admin, AlasanModerasi::InformasiPalsu,
            'Nominal anggaran belum pernah diumumkan secara resmi.',
        ));
        if ($riwayatDikira?->sanksi) {
            $this->pada(now()->subDays(14)->setTime(13, 20), fn () => $this->sanksi->cabut(
                $riwayatDikira->sanksi, Sanksi::PENCABUTAN_DIBATALKAN, $admin,
                'Setelah dicek ke Kasi Perencanaan, nominal tersebut memang sudah dibahas di Musdes. Sanksi dibatalkan karena keliru.',
            ));
        }

        // ---------------------------------------------------------------
        // 4. Budi: aspirasi kembar -> dihapus (Duplikat), tidak memicu sanksi
        // ---------------------------------------------------------------
        $aspirasiDuplikat = $this->pada(now()->subDays(8)->setTime(7, 10), fn () => $this->aspirasi(
            $budi,
            $kategoriFasilitas,
            'Usulan Perbaikan Jalan Lingkungan RT 002',
            'Mengulang usulan sebelumnya: jalan RT 002 Dusun Sukasari rusak dan perlu diperbaiki.',
            'Jl. Flamboyan, RT 002 / RW 003',
        ));
        $this->pada(now()->subDays(8)->setTime(11, 45), fn () => $this->moderasi->hapus(
            $aspirasiDuplikat, $admin, AlasanModerasi::Duplikat,
            'Sama dengan aspirasi "Usulan Perbaikan Jalan Lingkungan RT 002 Dusun Sukasari" yang sudah ada.',
        ));

        // ---------------------------------------------------------------
        // 5. Dewi: aspirasi disembunyikan sementara untuk dicek, lalu ditampilkan kembali
        // ---------------------------------------------------------------
        $aspirasiDicek = $this->pada(now()->subDays(7)->setTime(16, 0), fn () => $this->aspirasi(
            $dewi,
            $kategoriFasilitas,
            'Lampu Penerangan Jalan Dusun Cikadu Mati Sejak Sebulan',
            'Empat titik lampu penerangan jalan di jalur menuju makam Dusun Cikadu padam sejak bulan lalu sehingga rawan saat malam hari. Mohon segera diperbaiki.',
            'Jalur makam Dusun Cikadu',
        ));
        $this->pada(now()->subDays(6)->setTime(8, 20), fn () => $this->moderasi->sembunyikan($aspirasiDicek, $admin));
        $this->pada(now()->subDays(5)->setTime(14, 10), fn () => $this->moderasi->tampilkan($aspirasiDicek, $admin));

        // ---------------------------------------------------------------
        // 6. Eko: aspirasi spam kedua -> dihapus (Spam), tingkat 2 = 7 hari, MASIH BERLAKU
        // ---------------------------------------------------------------
        $spamKedua = $this->pada(now()->subDays(3)->setTime(21, 5), fn () => $this->aspirasi(
            $eko,
            $kategoriLainnya,
            'Peluang Usaha Sampingan Penghasilan Jutaan per Hari',
            'Mau penghasilan tambahan tanpa modal? Gabung grup investasi kami, dijamin untung. Link pendaftaran ada di bio, kuota terbatas untuk warga Banjarsari!',
            'Desa Banjarsari',
        ));
        $this->pada(now()->subDays(3)->setTime(21, 40), fn () => $this->moderasi->hapus(
            $spamKedua, $admin, AlasanModerasi::Spam,
            'Promosi investasi yang tidak jelas. Pelanggaran kedua dalam 180 hari.',
        ));

        // ---------------------------------------------------------------
        // 7. Eko: komentar tidak relevan -> disembunyikan
        // ---------------------------------------------------------------
        $komentarTidakRelevan = $this->pada(now()->subDays(2)->setTime(10, 0), fn () => $this->komentar(
            $aspirasiDicek,
            $eko,
            'Ada yang mau beli motor bekas? Kondisi mulus, surat lengkap, nego tipis.',
        ));
        $this->pada(now()->subDays(2)->setTime(10, 35), fn () => $this->moderasi->sembunyikan($komentarTidakRelevan, $admin));

        // ---------------------------------------------------------------
        // 8. Dewi: komentar menghina perangkat desa -> dihapus (Kata Kasar), 3 hari, MASIH BERLAKU
        // ---------------------------------------------------------------
        $komentarKasar = $this->pada(now()->subDay()->setTime(22, 15), fn () => $this->komentar(
            $aspirasiDicek,
            $dewi,
            'Perangkat desanya memang tidak becus dan pemalas semua, lampu mati sebulan dibiarkan saja!',
        ));
        $this->pada(now()->subDay()->setTime(22, 50), fn () => $this->moderasi->hapus(
            $komentarKasar, $admin, AlasanModerasi::KataKasar,
            'Kritik diperbolehkan, namun komentar ini mengandung hinaan kepada perangkat desa.',
        ));
    }

    /**
     * Jalankan aksi seolah-olah terjadi pada waktu tertentu.
     *
     * @template T
     *
     * @param  Closure(): T  $aksi
     * @return T
     */
    private function pada(Carbon $waktu, Closure $aksi): mixed
    {
        Carbon::setTestNow($waktu);

        try {
            return $aksi();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function aspirasi(User $warga, Kategori $kategori, string $judul, string $deskripsi, string $lokasi): Pengaduan
    {
        return Pengaduan::create([
            'user_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'jenis' => JenisLaporan::Aspirasi,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'lokasi' => $lokasi,
            'status' => StatusLaporan::Diajukan,
        ]);
    }

    private function komentar(Pengaduan $aspirasi, User $warga, string $isi): Komentar
    {
        return Komentar::create([
            'pengaduan_id' => $aspirasi->id,
            'user_id' => $warga->id,
            'isi' => $isi,
        ]);
    }
}
