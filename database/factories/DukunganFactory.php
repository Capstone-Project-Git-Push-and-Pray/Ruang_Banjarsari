<?php

namespace Database\Factories;

use App\Models\Dukungan;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dukungan>
 */
class DukunganFactory extends Factory
{
    protected $model = Dukungan::class;

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengaduan_id' => Pengaduan::factory()->aspirasi(),
            'user_id' => User::factory(),
        ];
    }
}
