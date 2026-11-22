<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lampiran extends Model
{
    use HasFactory;

    /**
     * Disk privat; berkas hanya dapat dibuka lewat rute berotorisasi (PBI #38).
     */
    public const DISK = 'local';

    public const FOLDER = 'lampiran-pengaduan';

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

    /**
     * URL berkas lampiran yang memeriksa hak akses pembukanya (PBI #25, PBI #38).
     */
    public function getUrlAttribute(): string
    {
        return route('lampiran.show', $this);
    }

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
