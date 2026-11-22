<?php

namespace Database\Factories;

use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Komentar>
 */
class KomentarFactory extends Factory
{
    protected $model = Komentar::class;

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengaduan_id' => Pengaduan::factory(),
            'user_id' => User::factory(),
            'isi' => fake()->paragraph(),
        ];
    }
}
