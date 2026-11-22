<?php

namespace App\Models;

use Database\Factories\DukunganFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dukungan extends Model
{
    /** @use HasFactory<DukunganFactory> */
    use HasFactory;

    protected $table = 'dukungan';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pengaduan_id',
        'user_id',
    ];

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Aspirasi yang didukung.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    /**
     * Warga yang memberi dukungan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
