<?php

namespace App\Models;

use App\Models\Concerns\DapatDimoderasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Komentar extends Model
{
    use DapatDimoderasi, HasFactory, SoftDeletes;

    protected $table = 'komentar';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'isi',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'disembunyikan_pada' => 'datetime',
        ];
    }

    /**
     * Batasi ke komentar yang boleh dilihat pengguna (PBI #44).
     * Perangkat desa melihat semua; warga tidak melihat komentar tersembunyi kecuali tulisannya sendiri.
     *
     * @param  Builder<Komentar>  $query
     */
    public function scopeTampilUntuk(Builder $query, User $user): void
    {
        if ($user->isAdminDesa() || $user->isKepalaDesa()) {
            return;
        }

        $query->where(fn (Builder $sub) => $sub->tampil()->orWhere('user_id', $user->id));
    }

    /**
     * Batasi ke komentar publik dari selain pemilik laporan (PBI #43).
     * Dipakai di dalam subquery hitung relasi komentar milik laporan.
     *
     * @param  Builder<Komentar>  $query
     */
    public function scopeKomentarMasuk(Builder $query): void
    {
        $query->tampil()->whereColumn('komentar.user_id', '!=', 'pengaduan.user_id');
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Laporan pengaduan tempat komentar diberikan.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    /**
     * Pengguna yang menulis komentar.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
