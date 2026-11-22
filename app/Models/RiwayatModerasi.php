<?php

namespace App\Models;

use App\Enums\AksiModerasi;
use App\Enums\AlasanModerasi;
use App\Enums\JenisKonten;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatModerasi extends Model
{
    protected $table = 'riwayat_moderasi';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'jenis_konten',
        'pengaduan_id',
        'komentar_id',
        'pemilik_id',
        'aksi',
        'alasan',
        'catatan',
        'isi_konten',
        'dilakukan_oleh',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_konten' => JenisKonten::class,
            'aksi' => AksiModerasi::class,
            'alasan' => AlasanModerasi::class,
        ];
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Aspirasi terkait, termasuk yang sudah dihapus lewat moderasi.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class)->withTrashed();
    }

    /**
     * Warga penulis konten.
     */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /**
     * Admin Desa yang mengambil keputusan.
     */
    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
