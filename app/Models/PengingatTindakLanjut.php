<?php

namespace App\Models;

use Database\Factories\PengingatTindakLanjutFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengingat dari Kepala Desa kepada Admin Desa tentang laporan yang belum ditanggapi (PBI #50, #51, #54).
 */
class PengingatTindakLanjut extends Model
{
    /** @use HasFactory<PengingatTindakLanjutFactory> */
    use HasFactory;

    protected $table = 'pengingat_tindak_lanjut';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pengaduan_id',
        'dikirim_oleh',
        'catatan',
        'selesai_pada',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selesai_pada' => 'datetime',
        ];
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Laporan yang diingatkan (tetap termuat walau laporan dihapus lunak).
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class)->withTrashed();
    }

    /**
     * Kepala Desa yang mengirim pengingat.
     */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    // -------------------------------------------------------
    // Scope & Helper
    // -------------------------------------------------------

    /**
     * Pengingat yang belum selesai (laporan belum ditindaklanjuti).
     */
    public function scopeAktif(Builder $query): void
    {
        $query->whereNull('selesai_pada');
    }
}
