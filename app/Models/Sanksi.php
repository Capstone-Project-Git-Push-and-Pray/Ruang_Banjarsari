<?php

namespace App\Models;

use App\Enums\FiturSanksi;
use App\Enums\StatusSanksi;
use Database\Factories\SanksiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanksi extends Model
{
    /** @use HasFactory<SanksiFactory> */
    use HasFactory;

    /**
     * Rentang waktu pelanggaran yang dihitung untuk menentukan tingkat sanksi berikutnya.
     */
    public const MASA_HITUNG_HARI = 180;

    public const PENCABUTAN_DIAKHIRI = 'diakhiri';

    public const PENCABUTAN_DIBATALKAN = 'dibatalkan';

    protected $table = 'sanksi';

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'fitur',
        'tingkat',
        'riwayat_moderasi_id',
        'mulai_pada',
        'berakhir_pada',
        'permanen',
        'dicabut_pada',
        'jenis_pencabutan',
        'catatan_admin',
        'diubah_oleh',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fitur' => FiturSanksi::class,
            'tingkat' => 'integer',
            'mulai_pada' => 'datetime',
            'berakhir_pada' => 'datetime',
            'permanen' => 'boolean',
            'dicabut_pada' => 'datetime',
        ];
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Warga yang terkena sanksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Keputusan moderasi yang memicu sanksi.
     */
    public function riwayatModerasi(): BelongsTo
    {
        return $this->belongsTo(RiwayatModerasi::class);
    }

    /**
     * Admin Desa yang terakhir mengubah sanksi.
     */
    public function diubahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }

    // -------------------------------------------------------
    // Scope & Status
    // -------------------------------------------------------

    /**
     * Batasi ke sanksi yang sedang membatasi fitur warga.
     *
     * @param  Builder<Sanksi>  $query
     */
    public function scopeBerlaku(Builder $query): void
    {
        $query->whereNull('dicabut_pada')
            ->where(fn (Builder $sub) => $sub->where('permanen', true)->orWhere('berakhir_pada', '>', now()));
    }

    /**
     * Batasi ke pelanggaran yang dihitung untuk tingkat sanksi berikutnya.
     * Sanksi yang dibatalkan karena kesalahan moderasi tidak dihitung.
     *
     * @param  Builder<Sanksi>  $query
     */
    public function scopeDihitung(Builder $query): void
    {
        $query->where('mulai_pada', '>=', now()->subDays(self::MASA_HITUNG_HARI))
            ->where(fn (Builder $sub) => $sub->whereNull('jenis_pencabutan')
                ->orWhere('jenis_pencabutan', '!=', self::PENCABUTAN_DIBATALKAN));
    }

    /**
     * Status sanksi pada saat ini.
     */
    public function status(): StatusSanksi
    {
        return match (true) {
            $this->jenis_pencabutan === self::PENCABUTAN_DIBATALKAN => StatusSanksi::Dibatalkan,
            $this->dicabut_pada !== null => StatusSanksi::Diakhiri,
            $this->permanen, $this->berakhir_pada?->isFuture() => StatusSanksi::Berlaku,
            $this->berakhir_pada?->equalTo($this->mulai_pada) => StatusSanksi::Peringatan,
            default => StatusSanksi::Berakhir,
        };
    }

    /**
     * Cek apakah sanksi sedang membatasi fitur warga.
     */
    public function isBerlaku(): bool
    {
        return $this->status() === StatusSanksi::Berlaku;
    }

    /**
     * Keterangan masa berlaku untuk UI.
     */
    public function keteranganMasaBerlaku(): string
    {
        return match (true) {
            $this->permanen => 'Permanen, sampai dibuka kembali oleh Admin Desa',
            $this->status() === StatusSanksi::Peringatan => 'Peringatan, tanpa pembatasan fitur',
            default => 'Sampai '.$this->berakhir_pada->translatedFormat('d M Y, H:i').' WIB',
        };
    }
}
