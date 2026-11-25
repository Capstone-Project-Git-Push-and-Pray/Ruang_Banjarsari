<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigasi_halaman_memakai_tampilan_berbahasa_indonesia(): void
    {
        Kategori::factory()->count(16)->create();

        $this->actingAs(User::factory()->adminDesa()->create())
            ->get(route('admin.kategori.index'))
            ->assertOk()
            ->assertSee(['Navigasi halaman', 'Menampilkan', 'Halaman berikutnya', 'Berikutnya'])
            ->assertDontSee('Showing');
    }

    public function test_tombol_halaman_pertama_muncul_mulai_halaman_ketiga(): void
    {
        Kategori::factory()->count(50)->create();
        $this->actingAs(User::factory()->adminDesa()->create());

        $this->get(route('admin.kategori.index', ['page' => 2]))
            ->assertOk()
            ->assertDontSee('Halaman Pertama');

        $this->get(route('admin.kategori.index', ['page' => 3]))
            ->assertOk()
            ->assertSee(['Halaman Pertama', 'Ke halaman pertama', route('admin.kategori.index', ['page' => 1])], false);
    }
}
