<?php

namespace App\Policies;

use App\Models\Komentar;
use App\Models\User;

class KomentarPolicy
{
    /**
     * Kebijakan otorisasi untuk model Komentar.
     *
     * TODO PBI #16: Implementasikan aturan otorisasi:
     * - Pengguna hanya dapat mengubah/menghapus komentar miliknya sendiri.
     * - Admin Desa dapat memoderasi komentar jika diperlukan.
     */

    /**
     * Tentukan apakah user dapat membuat komentar.
     */
    public function create(User $user): bool
    {
        // TODO PBI #16: Logika otorisasi create komentar
        return true;
    }

    /**
     * Tentukan apakah user dapat memperbarui komentar.
     */
    public function update(User $user, Komentar $komentar): bool
    {
        // TODO PBI #16: Logika otorisasi update komentar
        return true;
    }

    /**
     * Tentukan apakah user dapat menghapus komentar.
     */
    public function delete(User $user, Komentar $komentar): bool
    {
        // TODO PBI #16: Logika otorisasi delete komentar
        return true;
    }
}
