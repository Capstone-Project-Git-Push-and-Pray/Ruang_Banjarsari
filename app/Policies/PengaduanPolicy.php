<?php

namespace App\Policies;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use App\Models\User;

class PengaduanPolicy
{
    /**
     * Tentukan apakah user dapat melihat daftar pengaduan.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah user dapat melihat detail pengaduan (PBI #22).
     * Kriteria:
     * - Admin Desa dan Kepala Desa dapat melihat seluruh pengaduan desa.
     * - Warga hanya dapat membuka laporannya sendiri (tidak bisa membuka laporan warga lain lewat URL).
     */
    public function view(User $user, Pengaduan $pengaduan): bool
    {
        if ($user->isAdminDesa() || $user->isKepalaDesa()) {
            return true;
        }

        return $user->isWarga() && (int) $user->id === (int) $pengaduan->user_id;
    }

    /**
     * Tentukan apakah user berhak melihat data pribadi pelapor (PBI #22).
     * Kriteria: Data pelapor hanya terlihat oleh Admin Desa, Kepala Desa, dan warga pemilik laporan.
     */
    public function viewDataPelapor(User $user, Pengaduan $pengaduan): bool
    {
        if ($user->isAdminDesa() || $user->isKepalaDesa()) {
            return true;
        }

        return $user->isWarga() && (int) $user->id === (int) $pengaduan->user_id;
    }

    /**
     * Tentukan apakah user dapat membuat pengaduan baru.
     * Hanya warga dengan akun berstatus Aktif yang diizinkan melapor.
     */
    public function create(User $user): bool
    {
        return $user->isWarga() && $user->isAktif();
    }

    /**
     * Tentukan apakah user dapat memperbarui pengaduan.
     * Hanya pemilik yang boleh mengubah laporannya selama status masih diajukan.
     */
    public function update(User $user, Pengaduan $pengaduan): bool
    {
        return $user->isWarga()
            && (int) $user->id === (int) $pengaduan->user_id
            && $pengaduan->status === StatusLaporan::Diajukan;
    }

    /**
     * Tentukan apakah user dapat menghapus pengaduan.
     * Hanya pemilik yang boleh menghapus laporannya selama status masih diajukan.
     */
    public function delete(User $user, Pengaduan $pengaduan): bool
    {
        return $user->isWarga()
            && (int) $user->id === (int) $pengaduan->user_id
            && $pengaduan->status === StatusLaporan::Diajukan;
    }
}
