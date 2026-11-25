<?php

namespace Database\Seeders;

use App\Enums\JenisLaporan;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Contoh diskusi komentar antarwarga aktif pada aspirasi publik (PBI #42).
 * Satu aspirasi diberi banyak komentar untuk menguji gulir daftar komentar.
 */
class KomentarSeeder extends Seeder
{
    /**
     * Isi contoh komentar warga.
     */
    public function run(): void
    {
        $warga = [
            'budi' => User::where('nik', '9999000000000001')->first(),
            'dewi' => User::where('nik', '9999000000000004')->first(),
            'eko' => User::where('nik', '9999000000000005')->first(),
        ];

        if (in_array(null, $warga, true)) {
            $this->command?->warn('Warga contoh belum tersedia. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        foreach ($this->diskusi() as $judul => $daftarKomentar) {
            $aspirasi = Pengaduan::where('jenis', JenisLaporan::Aspirasi)->where('judul', $judul)->first();

            if (! $aspirasi || $aspirasi->komentar()->whereIn('user_id', collect($warga)->pluck('id'))->where('isi', $daftarKomentar[0][1])->exists()) {
                continue;
            }

            $waktu = $aspirasi->created_at->copy();

            foreach ($daftarKomentar as [$penulis, $isi, $jedaMenit]) {
                $waktu = $waktu->copy()->addMinutes($jedaMenit);

                if ($waktu->isFuture()) {
                    $waktu = now()->subMinutes(5);
                }

                $komentar = new Komentar([
                    'pengaduan_id' => $aspirasi->id,
                    'user_id' => $warga[$penulis]->id,
                    'isi' => $isi,
                ]);
                $komentar->created_at = $waktu;
                $komentar->updated_at = $waktu;
                $komentar->save();
            }
        }
    }

    /**
     * Daftar komentar per judul aspirasi: [penulis, isi, jeda menit dari komentar sebelumnya].
     *
     * @return array<string, list<array{0: string, 1: string, 2: int}>>
     */
    private function diskusi(): array
    {
        return [
            'Usulan Pengadaan Wi-Fi Gratis Publik di Balai Dusun Sukasari' => [
                ['dewi', 'Setuju sekali, anak saya sering ke warung kopi cuma buat numpang Wi-Fi untuk kerjakan tugas sekolah.', 45],
                ['eko', 'Ide bagus. Tapi siapa yang bayar tagihan internetnya tiap bulan? Jangan sampai cuma jalan dua bulan lalu mati.', 30],
                ['budi', 'Usulan saya biayanya masuk anggaran pemeliharaan balai dusun, Pak Eko. Bisa dibahas juga di Musdes nanti.', 20],
                ['dewi', 'Kalau bisa jam pakainya dibatasi, misalnya sampai jam 9 malam, supaya balai tidak jadi tempat nongkrong sampai larut.', 90],
                ['eko', 'Setuju soal pembatasan jam. Kata sandinya juga sebaiknya diganti rutin.', 15],
                ['budi', 'Betul, nanti bisa dipegang pengurus karang taruna supaya ada yang bertanggung jawab.', 25],
                ['dewi', 'Karang taruna Sukasari sudah aktif lagi kan? Kemarin saya lihat mereka bersih-bersih balai.', 180],
                ['eko', 'Sudah aktif Bu, tiap Minggu pagi ada kegiatan. Mereka pasti mau bantu.', 40],
                ['budi', 'Saya coba hubungi ketua karang tarunanya minggu ini untuk minta dukungan tertulis.', 60],
                ['dewi', 'Sekalian usul colokan listrik di balai ditambah, sekarang cuma ada dua dan sering rebutan.', 300],
                ['eko', 'Iya, dan lampunya juga perlu diganti. Kalau sore sudah redup, susah dipakai belajar.', 35],
                ['budi', 'Catatan colokan dan lampu saya tambahkan ke usulan ini. Terima kasih masukannya.', 50],
                ['dewi', 'Semoga cepat ditinjau. Banyak anak SMP di Sukasari yang belum punya kuota sendiri.', 720],
                ['eko', 'Aamiin. Saya bantu dukung juga di Musdes nanti.', 20],
            ],
            'Lampu Penerangan Jalan Dusun Cikadu Mati Sejak Sebulan' => [
                ['budi', 'Benar Bu, saya lewat jalur itu tiap pulang kerja dan gelap sekali setelah magrib.', 120],
                ['eko', 'Dua minggu lalu ada motor yang jatuh di tikungan dekat makam karena tidak kelihatan lubang di jalan.', 45],
                ['dewi', 'Astaghfirullah, semoga pengendaranya tidak apa-apa. Itu sebabnya saya laporkan.', 30],
                ['budi', 'Sambil menunggu, mungkin warga bisa pasang lampu sementara dari rumah terdekat?', 240],
                ['dewi', 'Rumah terdekat milik Pak RT, nanti saya coba tanyakan langsung ke beliau.', 60],
            ],
            'Gagasan Pembentukan Bank Sampah & Pengomposan Organik Mandiri Dusun Krajan' => [
                ['dewi', 'Saya siap ikut jadi nasabah pertama. Sampah plastik di rumah numpuk terus tiap minggu.', 200],
                ['eko', 'Kalau ada timbangan dan harga per kilo yang jelas, warga pasti lebih semangat memilah.', 55],
                ['dewi', 'Kompos organiknya juga bisa dipakai kelompok tani, jadi tidak perlu beli pupuk terus.', 140],
                ['eko', 'Usul tempatnya di lahan kosong belakang posyandu, cukup luas dan jauh dari rumah warga.', 75],
            ],
            'Usulan Perbaikan Jalan Lingkungan RT 002 Dusun Sukasari' => [
                ['dewi', 'Jalan ini juga dipakai anak-anak ke SD, kalau hujan genangannya sampai mata kaki.', 300],
                ['eko', 'Mungkin bisa dimulai dari perbaikan saluran air di pinggir jalan dulu supaya airnya tidak naik ke jalan.', 90],
                ['budi', 'Usul yang bagus Pak Eko, saya tambahkan ke catatan untuk Musdes.', 45],
            ],
        ];
    }
}
