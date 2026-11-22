<?php

namespace App\Policies;

use App\Models\Komentar;
use App\Models\User;

class KomentarPolicy
{
    /**
     * Tentukan apakah user dapat membuat komentar.
     * Pengguna terautentikasi (Warga aktif, Admin Desa, Kepala Desa) dapat membuat komentar.
     */
    public function create(User $user): bool
    {
        if ($user->isWarga()) {
            return $user->isAktif();
        }

        return $user->isAdminDesa() || $user->isKepalaDesa();
    }

    /**
     * Tentukan apakah user dapat memperbarui komentar.
     * Hanya pemilik komentar, selama komentar dan laporannya tidak sedang disembunyikan.
     */
    public function update(User $user, Komentar $komentar): bool
    {
        return (int) $user->id === (int) $komentar->user_id
            && ! $this->terkunciModerasi($komentar);
    }

    /**
     * Tentukan apakah user dapat menghapus komentar tanpa alasan.
     * Pemilik komentar, atau Admin Desa pada pengaduan privat. Komentar warga pada aspirasi
     * hanya dapat dihapus Admin Desa lewat moderasi yang mencatat alasan (PBI #45).
     */
    public function delete(User $user, Komentar $komentar): bool
    {
        if ((int) $user->id === (int) $komentar->user_id) {
            return ! $this->terkunciModerasi($komentar);
        }

        return $user->isAdminDesa() && $komentar->pengaduan?->isPengaduan() === true;
    }

    /**
     * Tentukan apakah user dapat memoderasi komentar (PBI #44, #45).
     * Hanya Admin Desa, pada komentar warga di aspirasi publik.
     */
    public function moderasi(User $user, Komentar $komentar): bool
    {
        return $user->isAdminDesa()
            && $komentar->pengaduan?->isAspirasi() === true
            && $komentar->user->isWarga();
    }

    /**
     * Konten yang sedang dimoderasi tidak boleh diubah atau dihilangkan penulisnya.
     */
    private function terkunciModerasi(Komentar $komentar): bool
    {
        return $komentar->isDisembunyikan()
            || $komentar->pengaduan === null
            || $komentar->pengaduan->isDisembunyikan();
    }
}
