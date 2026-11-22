<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Perilaku konten publik yang dapat disembunyikan Admin Desa (PBI #44).
 */
trait DapatDimoderasi
{
    /**
     * Admin Desa yang menyembunyikan konten ini.
     */
    public function disembunyikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disembunyikan_oleh');
    }

    /**
     * Cek apakah konten sedang disembunyikan dari publik.
     */
    public function isDisembunyikan(): bool
    {
        return $this->disembunyikan_pada !== null;
    }

    /**
     * Batasi ke konten yang tidak sedang disembunyikan.
     *
     * @param  Builder<static>  $query
     */
    public function scopeTampil(Builder $query): void
    {
        $query->whereNull($this->qualifyColumn('disembunyikan_pada'));
    }
}
