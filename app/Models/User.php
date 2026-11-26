<?php

namespace App\Models;

use App\Enums\FiturSanksi;
use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'nama_lengkap',
        'tanggal_lahir',
        'alamat',
        'rt',
        'rw',
        'dusun',
        'email',
        'telepon',
        'whatsapp',
        'password',
        'role',
        'status_akun',
        'alasan_penolakan',
        'ditinjau_oleh',
        'ditinjau_pada',
        'persetujuan_pada',
        'wajib_ganti_password',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'status_akun' => StatusAkun::class,
            'tanggal_lahir' => 'date',
            'email_verified_at' => 'datetime',
            'ditinjau_pada' => 'datetime',
            'persetujuan_pada' => 'datetime',
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }

    // -------------------------------------------------------
    // Helper peran
    // -------------------------------------------------------

    public function isWarga(): bool
    {
        return $this->role === Role::Warga;
    }

    public function isAdminDesa(): bool
    {
        return $this->role === Role::AdminDesa;
    }

    public function isKepalaDesa(): bool
    {
        return $this->role === Role::KepalaDesa;
    }

    /**
     * Batas waktu tidak aktif (menit) sebelum sesi berakhir, sesuai peran.
     */
    public function batasTidakAktifMenit(): int
    {
        return (int) config('sesi.batas_tidak_aktif_menit.'.$this->role->value, config('session.lifetime'));
    }

    /**
     * Fitur "Ingat saya di perangkat ini" hanya untuk warga.
     */
    public function bolehIngatSaya(): bool
    {
        return $this->isWarga();
    }

    public function isAktif(): bool
    {
        return $this->status_akun === StatusAkun::Aktif;
    }

    public function isMenungguTinjauan(): bool
    {
        return $this->status_akun === StatusAkun::MenungguTinjauan;
    }

    public function isDitolak(): bool
    {
        return $this->status_akun === StatusAkun::Ditolak;
    }

    /**
     * Mutator agar nama lengkap selalu disimpan dalam huruf kapital (capslock) rapi.
     */
    public function setNamaLengkapAttribute(?string $value): void
    {
        $this->attributes['nama_lengkap'] = $value !== null ? Str::of($value)->squish()->upper()->toString() : null;
    }

    /**
     * Kirim email atur ulang kata sandi berbahasa Indonesia bertema SILAFA.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Accessor untuk kompatibilitas nama pengguna.
     */
    public function getNameAttribute(): ?string
    {
        return $this->nama_lengkap;
    }

    /**
     * Mutator untuk kompatibilitas nama pengguna.
     */
    public function setNameAttribute(?string $value): void
    {
        $this->setNamaLengkapAttribute($value);
    }

    /**
     * Nama pengguna yang boleh dilihat oleh pengguna lain.
     *
     * Nama lengkap sesuai KTP warga adalah data pribadi (PBI #22), sehingga warga lain
     * hanya melihat nama publiknya.
     */
    public function namaUntuk(self $pelihat): string
    {
        if (! $this->isWarga() || ! $pelihat->isWarga() || $this->is($pelihat)) {
            return (string) $this->nama_lengkap;
        }

        return $this->namaPublik();
    }

    /**
     * Nama depan dan inisial, misalnya "Budi S.".
     */
    public function namaPublik(): string
    {
        $bagianNama = Str::of((string) $this->nama_lengkap)->squish()->lower()->title()->explode(' ');
        $inisial = $bagianNama->skip(1)->map(fn (string $bagian) => mb_substr($bagian, 0, 1).'.');

        return $inisial->prepend($bagianNama->first())->implode(' ');
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Petugas/Admin desa yang meninjau pendaftaran warga.
     */
    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(self::class, 'ditinjau_oleh');
    }

    /**
     * Pengaduan yang dimiliki user.
     */
    public function pengaduan(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    /**
     * Sanksi fitur yang pernah diterima user (PBI #46, PBI #47).
     */
    public function sanksi(): HasMany
    {
        return $this->hasMany(Sanksi::class);
    }

    /**
     * Sanksi yang sedang membatasi fitur tertentu, jika ada (PBI #48).
     */
    public function sanksiBerlaku(FiturSanksi $fitur): ?Sanksi
    {
        return $this->sanksi()
            ->berlaku()
            ->where('fitur', $fitur)
            ->orderByDesc('permanen')
            ->orderByDesc('berakhir_pada')
            ->first();
    }

    /**
     * Komentar yang ditulis user.
     */
    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar::class);
    }
}
