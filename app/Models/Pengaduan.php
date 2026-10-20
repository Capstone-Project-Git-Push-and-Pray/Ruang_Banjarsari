<?php

namespace App\Models;

use App\Enums\JenisLaporan;
use App\Enums\StatusLaporan;
use Database\Factories\PengaduanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengaduan extends Model
{
    /** @use HasFactory<PengaduanFactory> */
    use HasFactory;

    protected $table = 'pengaduan';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'jenis',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'status',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis' => JenisLaporan::class,
            'status' => StatusLaporan::class,
        ];
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Pengguna yang mengajukan laporan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kategori dari laporan.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Lampiran berkas pendukung laporan.
     */
    public function lampiran(): HasMany
    {
        return $this->hasMany(Lampiran::class);
    }

    /**
     * Komentar atau tanggapan pada laporan.
     */
    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar::class);
    }

    /**
     * Riwayat perubahan status laporan.
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class);
    }
}
