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
     * Komentar atau tanggapan pada laporan (PBI #33, PBI #34).
     */
    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar::class)->oldest();
    }

    /**
     * Riwayat perubahan status laporan.
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class)->oldest();
    }

    // -------------------------------------------------------
    // Alur Status (PBI #29)
    // -------------------------------------------------------

    /**
     * Cek apakah laporan bertipe Aspirasi.
     */
    public function isAspirasi(): bool
    {
        return $this->jenis === JenisLaporan::Aspirasi;
    }

    /**
     * Cek apakah laporan bertipe Pengaduan.
     */
    public function isPengaduan(): bool
    {
        return $this->jenis === JenisLaporan::Pengaduan;
    }

    /**
     * Dapatkan daftar status berikutnya yang diizinkan untuk laporan ini (PBI #29).
     *
     * Alur pemrosesan dibedakan secara terstruktur:
     * - Aspirasi: Diajukan -> Ditinjau -> Ditampung -> [Dalam Pembahasan / Direalisasikan] -> [Direalisasikan / Tidak Dapat Ditindaklanjuti]
     * - Pengaduan: Diajukan -> Ditinjau -> Diproses -> [Selesai / Ditolak]
     *
     * @return array<StatusLaporan>
     */
    public function allowedNextStatuses(): array
    {
        if ($this->jenis === JenisLaporan::Aspirasi) {
            return match ($this->status) {
                // 1. Diajukan -> Ditinjau (Lanjutkan) ATAU Tolak
                StatusLaporan::Diajukan => [
                    StatusLaporan::Ditinjau,
                    StatusLaporan::TidakDapatDitindaklanjuti,
                ],
                // 2. Ditinjau -> Ditampung (Lanjutkan) ATAU Tolak
                StatusLaporan::Ditinjau => [
                    StatusLaporan::Ditampung,
                    StatusLaporan::TidakDapatDitindaklanjuti,
                ],
                // 3. Ditampung -> Dalam Pembahasan / Direalisasikan (Lanjutkan) ATAU Tolak
                StatusLaporan::Ditampung => [
                    StatusLaporan::DalamPembahasan,
                    StatusLaporan::Direalisasikan,
                    StatusLaporan::TidakDapatDitindaklanjuti,
                ],
                // 4. Dalam Pembahasan -> Direalisasikan (Lanjutkan) ATAU Tolak
                StatusLaporan::DalamPembahasan => [
                    StatusLaporan::Direalisasikan,
                    StatusLaporan::TidakDapatDitindaklanjuti,
                ],
                // Kompatibilitas untuk data terdahulu
                StatusLaporan::Diproses => [
                    StatusLaporan::Direalisasikan,
                    StatusLaporan::TidakDapatDitindaklanjuti,
                    StatusLaporan::Selesai,
                ],
                default => [],
            };
        }

        return match ($this->status) {
            StatusLaporan::Diajukan => [StatusLaporan::Ditinjau, StatusLaporan::Ditolak],
            StatusLaporan::Ditinjau => [StatusLaporan::Diproses, StatusLaporan::Ditolak],
            StatusLaporan::Diproses => [StatusLaporan::Selesai, StatusLaporan::Ditolak],
            default => [],
        };
    }

    /**
     * Cek apakah status dapat diubah ke status tujuan.
     */
    public function canTransitionTo(StatusLaporan $targetStatus): bool
    {
        if (in_array($targetStatus, $this->allowedNextStatuses(), true)) {
            return true;
        }

        // Izinkan Ditolak sebagai alias TidakDapatDitindaklanjuti untuk aspirasi
        if ($this->isAspirasi() && $targetStatus === StatusLaporan::Ditolak) {
            return in_array(StatusLaporan::TidakDapatDitindaklanjuti, $this->allowedNextStatuses(), true);
        }

        return false;
    }
}
