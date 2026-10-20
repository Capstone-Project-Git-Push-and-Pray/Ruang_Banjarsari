<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komentar extends Model
{
    use HasFactory;

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
