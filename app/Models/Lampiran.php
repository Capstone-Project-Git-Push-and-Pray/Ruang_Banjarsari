<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lampiran extends Model
{
    use HasFactory;

    protected $table = 'lampiran';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pengaduan_id',
        'path',
        'nama_asli',
    ];

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Laporan pengaduan pemilik lampiran ini.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }
}
