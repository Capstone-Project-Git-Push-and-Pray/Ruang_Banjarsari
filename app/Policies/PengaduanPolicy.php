<?php

namespace App\Policies;

use App\Models\Pengaduan;
use App\Models\User;

class PengaduanPolicy
{
    /**
     * Kebijakan otorisasi untuk model Pengaduan.
     *
     * TODO PBI #16: Implementasikan aturan otorisasi:
     * - Warga hanya dapat melihat dan mengelola pengaduannya sendiri.
     * - Admin Desa dan Kepala Desa dapat meninjau seluruh pengaduan desa.
     */

    /**
     * Tentukan apakah user dapat melihat daftar pengaduan.
     */
    public function viewAny(User $user): bool
    {
        // TODO PBI #16: Logika otorisasi viewAny
        return true;
    }

    /**
     * Tentukan apakah user dapat melihat detail pengaduan.
     */
    public function view(User $user, Pengaduan $pengaduan): bool
    {
        // TODO PBI #16: Logika otorisasi view
        return true;
    }

    /**
     * Tentukan apakah user dapat membuat pengaduan baru.
     */
    public function create(User $user): bool
    {
        // TODO PBI #16: Logika otorisasi create
        return true;
    }

    /**
     * Tentukan apakah user dapat memperbarui pengaduan.
     */
    public function update(User $user, Pengaduan $pengaduan): bool
    {
        // TODO PBI #16: Logika otorisasi update
        return true;
    }

    /**
     * Tentukan apakah user dapat menghapus pengaduan.
     */
    public function delete(User $user, Pengaduan $pengaduan): bool
    {
        // TODO PBI #16: Logika otorisasi delete
        return true;
    }
}
