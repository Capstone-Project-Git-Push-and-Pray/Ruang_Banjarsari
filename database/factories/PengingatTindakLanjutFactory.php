<?php

namespace Database\Factories;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use App\Models\PengingatTindakLanjut;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengingatTindakLanjut>
 */
class PengingatTindakLanjutFactory extends Factory
{
    protected $model = PengingatTindakLanjut::class;

    /**
     * Definisi state default model: pengingat aktif untuk laporan Diajukan yang sudah 4 hari belum ditanggapi.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengaduan_id' => Pengaduan::factory()->state([
                'status' => StatusLaporan::Diajukan,
                'created_at' => now()->subDays(4),
            ]),
            'dikirim_oleh' => User::factory()->kepalaDesa(),
            'catatan' => fake()->optional()->sentence(),
            'selesai_pada' => null,
        ];
    }

    /**
     * State untuk pengingat yang sudah selesai karena laporan telah ditindaklanjuti.
     */
    public function selesai(): static
    {
        return $this->state(fn () => [
            'selesai_pada' => now(),
        ]);
    }
}
