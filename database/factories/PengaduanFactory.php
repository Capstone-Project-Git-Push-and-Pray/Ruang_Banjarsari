<?php

namespace Database\Factories;

use App\Enums\JenisLaporan;
use App\Enums\StatusLaporan;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengaduan>
 */
class PengaduanFactory extends Factory
{
    protected $model = Pengaduan::class;

    /**
     * Definisi state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'jenis' => JenisLaporan::Pengaduan,
            'kategori_id' => Kategori::factory(),
            'judul' => fake()->sentence(4),
            'deskripsi' => fake()->paragraph(),
            'lokasi' => fake()->address(),
            'status' => StatusLaporan::Diajukan,
        ];
    }

    /**
     * State untuk laporan berjenis aspirasi.
     */
    public function aspirasi(): static
    {
        return $this->state(fn () => [
            'jenis' => JenisLaporan::Aspirasi,
        ]);
    }

    /**
     * State untuk konten yang disembunyikan Admin Desa.
     */
    public function disembunyikan(): static
    {
        return $this->state(fn () => [
            'disembunyikan_pada' => now(),
            'disembunyikan_oleh' => User::factory()->adminDesa(),
        ]);
    }
}
