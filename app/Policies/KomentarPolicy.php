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
     * Hanya pemilik komentar yang dapat memperbarui komentarnya sendiri.
     */
    public function update(User $user, Komentar $komentar): bool
    {
        return (int) $user->id === (int) $komentar->user_id;
    }

    /**
     * Tentukan apakah user dapat menghapus komentar.
     * Pemilik komentar atau Admin Desa (tindakan moderasi).
     */
    public function delete(User $user, Komentar $komentar): bool
    {
        return $user->isAdminDesa() || ((int) $user->id === (int) $komentar->user_id);
    }
}
