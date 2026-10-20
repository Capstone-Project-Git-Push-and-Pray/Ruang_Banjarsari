<?php

namespace Database\Factories;

use App\Models\DataKependudukan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataKependudukan>
 */
class DataKependudukanFactory extends Factory
{
    protected $model = DataKependudukan::class;

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('9999############'),
            'nama_lengkap' => fake()->name(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-20 years'),
            'alamat' => fake()->streetAddress(),
            'rt' => str_pad((string) fake()->numberBetween(1, 10), 3, '0', STR_PAD_LEFT),
            'rw' => str_pad((string) fake()->numberBetween(1, 5), 3, '0', STR_PAD_LEFT),
            'dusun' => fake()->randomElement(['Dusun Krajan', 'Dusun Banjarsari Timur', 'Dusun Banjarsari Barat']),
        ];
    }
}
