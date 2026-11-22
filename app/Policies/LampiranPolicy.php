<?php

namespace App\Policies;

use App\Models\Lampiran;
use App\Models\User;

class LampiranPolicy
{
    /**
     * Tentukan apakah user dapat melihat foto lampiran (PBI #38).
     * Hak akses foto mengikuti hak akses laporannya: pemilik dan pihak desa,
     * ditambah seluruh warga aktif untuk aspirasi yang tidak disembunyikan.
     */
    public function view(User $user, Lampiran $lampiran): bool
    {
        $pengaduan = $lampiran->pengaduan;

        return $pengaduan !== null
            && ($user->can('view', $pengaduan) || $user->can('lihatPublik', $pengaduan));
    }
}
