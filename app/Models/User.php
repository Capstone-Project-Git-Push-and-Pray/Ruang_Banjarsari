<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\StatusAkun;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function isAktif(): bool
    {
        return $this->status_akun === StatusAkun::Aktif;
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
        $this->attributes['nama_lengkap'] = $value;
    }

    // -------------------------------------------------------
    // Relasi
    // -------------------------------------------------------

    /**
     * Pengaduan yang dimiliki user.
     */
    public function pengaduan(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    /**
     * Komentar yang ditulis user.
     */
    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar::class);
    }
}
