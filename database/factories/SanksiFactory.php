<?php

namespace Database\Factories;

use App\Enums\FiturSanksi;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sanksi>
 */
class SanksiFactory extends Factory
{
    protected $model = Sanksi::class;

    /**
     * Definisi state default model: sanksi aspirasi tingkat 2 yang sedang berlaku.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->warga(),
            'fitur' => FiturSanksi::Aspirasi,
            'tingkat' => 2,
            'mulai_pada' => now(),
            'berakhir_pada' => now()->addDays(7),
            'permanen' => false,
        ];
    }

    /**
     * State untuk sanksi komentar publik.
     */
    public function komentarPublik(): static
    {
        return $this->state(fn () => [
            'fitur' => FiturSanksi::KomentarPublik,
            'tingkat' => 1,
            'berakhir_pada' => now()->addDays(3),
        ]);
    }

    /**
     * State untuk sanksi yang masa berlakunya sudah lewat.
     */
    public function berakhir(): static
    {
        return $this->state(fn () => [
            'mulai_pada' => now()->subDays(10),
            'berakhir_pada' => now()->subDays(3),
        ]);
    }

    /**
     * State untuk sanksi yang dibatalkan Admin Desa.
     */
    public function dibatalkan(): static
    {
        return $this->state(fn () => [
            'dicabut_pada' => now(),
            'jenis_pencabutan' => Sanksi::PENCABUTAN_DIBATALKAN,
            'catatan_admin' => 'Kesalahan moderasi.',
        ]);
    }
}
