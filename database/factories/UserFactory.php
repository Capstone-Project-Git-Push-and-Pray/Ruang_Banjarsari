<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Password bawaan factory.
     */
    protected static ?string $password;

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('3372############'),
            'nama_lengkap' => fake()->name(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-18 years'),
            'alamat' => fake()->streetAddress(),
            'rt' => str_pad((string) fake()->numberBetween(1, 10), 3, '0', STR_PAD_LEFT),
            'rw' => str_pad((string) fake()->numberBetween(1, 5), 3, '0', STR_PAD_LEFT),
            'dusun' => fake()->randomElement(['Dusun Krajan', 'Dusun Banjarsari Timur', 'Dusun Banjarsari Barat']),
            'email' => fake()->unique()->safeEmail(),
            'telepon' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::Warga,
            'status_akun' => StatusAkun::Aktif,
            'wajib_ganti_password' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State untuk peran warga.
     */
    public function warga(): static
    {
        return $this->state(fn () => [
            'role' => Role::Warga,
        ]);
    }

    /**
     * State untuk peran admin desa.
     */
    public function adminDesa(): static
    {
        return $this->state(fn () => [
            'role' => Role::AdminDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);
    }

    /**
     * State untuk peran kepala desa.
     */
    public function kepalaDesa(): static
    {
        return $this->state(fn () => [
            'role' => Role::KepalaDesa,
            'status_akun' => StatusAkun::Aktif,
        ]);
    }

    /**
     * State status akun menunggu tinjauan.
     */
    public function menungguTinjauan(): static
    {
        return $this->state(fn () => [
            'status_akun' => StatusAkun::MenungguTinjauan,
        ]);
    }

    /**
     * State status akun ditolak.
     */
    public function ditolak(): static
    {
        return $this->state(fn () => [
            'status_akun' => StatusAkun::Ditolak,
            'alasan_penolakan' => 'Data tidak sesuai dengan kependudukan desa.',
        ]);
    }

    /**
     * State akun dengan email belum terverifikasi.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
