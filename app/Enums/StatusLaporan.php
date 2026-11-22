<?php

namespace App\Enums;

/**
 * Status laporan pengaduan/aspirasi.
 */
enum StatusLaporan: string
{
    case Diajukan = 'diajukan';
    case Ditinjau = 'ditinjau';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';

    // Status khusus alur Aspirasi Warga
    case Ditampung = 'ditampung';
    case DalamPembahasan = 'dalam_pembahasan';
    case Direalisasikan = 'direalisasikan';
    case TidakDapatDitindaklanjuti = 'tidak_dapat_ditindaklanjuti';

    /**
     * Label tampilan untuk UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::Ditinjau => 'Ditinjau',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak',
            self::Ditampung => 'Ditampung',
            self::DalamPembahasan => 'Dalam Pembahasan',
            self::Direalisasikan => 'Direalisasikan',
            self::TidakDapatDitindaklanjuti => 'Tidak Ditindaklanjuti',
        };
    }

    /**
     * Deskripsi ringkas tahapan alur aspirasi.
     */
    public function aspirasiDescription(): string
    {
        return match ($this) {
            self::Diajukan => 'Warga mengirimkan aspirasi',
            self::Ditinjau => 'Admin memeriksa aspirasi',
            self::Ditampung => 'Aspirasi diterima sebagai masukan pemerintah desa',
            self::DalamPembahasan => 'Sedang dibahas dan dipertimbangkan lebih lanjut',
            self::Direalisasikan => 'Aspirasi diwujudkan karena dapat dilaksanakan',
            self::TidakDapatDitindaklanjuti => 'Aspirasi tidak dapat ditindaklanjuti oleh pemerintah desa',
            default => '',
        };
    }

    /**
     * Badge penanda akhir (Selesai / Final).
     */
    public function badgeSubLabel(): ?string
    {
        return match ($this) {
            self::Direalisasikan, self::Selesai => 'Selesai',
            self::TidakDapatDitindaklanjuti, self::Ditolak => 'Final',
            default => null,
        };
    }

    /**
     * Kelas gaya warna Tailwind untuk badge status (solid bold dengan teks putih).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Diajukan => 'bg-amber-600 text-white border-amber-700',
            self::Ditinjau => 'bg-blue-600 text-white border-blue-700',
            self::Diproses => 'bg-indigo-600 text-white border-indigo-700',
            self::Selesai, self::Direalisasikan => 'bg-[#1F6B4F] text-white border-[#174B39]',
            self::Ditolak, self::TidakDapatDitindaklanjuti => 'bg-red-600 text-white border-red-700',
            self::Ditampung => 'bg-zinc-700 text-white border-zinc-800',
            self::DalamPembahasan => 'bg-sky-600 text-white border-sky-700',
        };
    }

    /**
     * Daftar status yang relevan untuk Pengaduan warga.
     *
     * @return array<self>
     */
    public static function pengaduanCases(): array
    {
        return [
            self::Diajukan,
            self::Ditinjau,
            self::Diproses,
            self::Selesai,
            self::Ditolak,
        ];
    }

    /**
     * Daftar status yang relevan untuk Aspirasi warga.
     *
     * @return array<self>
     */
    public static function aspirasiCases(): array
    {
        return [
            self::Diajukan,
            self::Ditinjau,
            self::Ditampung,
            self::DalamPembahasan,
            self::Direalisasikan,
            self::TidakDapatDitindaklanjuti,
        ];
    }
}
