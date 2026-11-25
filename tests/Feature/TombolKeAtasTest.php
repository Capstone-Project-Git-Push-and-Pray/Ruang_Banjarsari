<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TombolKeAtasTest extends TestCase
{
    use RefreshDatabase;

    public function test_tombol_kembali_ke_atas_tampil_untuk_setiap_peran(): void
    {
        $pengguna = [
            User::factory()->warga()->create(),
            User::factory()->adminDesa()->create(),
            User::factory()->kepalaDesa()->create(),
        ];

        foreach ($pengguna as $user) {
            $this->actingAs($user)
                ->get(route('profile.edit'))
                ->assertOk()
                ->assertSee('Kembali ke atas halaman');
        }
    }

    public function test_tombol_kembali_ke_atas_tampil_di_beranda_publik(): void
    {
        $this->get('/')->assertOk()->assertSee('Kembali ke atas halaman');
    }
}
