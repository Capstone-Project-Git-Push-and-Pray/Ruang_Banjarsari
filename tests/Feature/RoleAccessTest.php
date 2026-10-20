<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_belum_login_diarahkan_ke_login(): void
    {
        $this->get('/warga/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/kepala/dashboard')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_warga_dapat_mengakses_dashboard_warga(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get('/warga/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Warga - placeholder');
    }

    public function test_warga_tidak_bisa_mengakses_rute_admin_desa(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_warga_tidak_bisa_mengakses_rute_kepala_desa(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get('/kepala/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_desa_bisa_mengakses_rute_admin(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin Desa - placeholder');
    }

    public function test_admin_desa_tidak_bisa_mengakses_rute_kepala_desa(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/kepala/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_desa_tidak_bisa_mengakses_rute_warga(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $response = $this->actingAs($admin)->get('/warga/dashboard');

        $response->assertStatus(403);
    }

    public function test_kepala_desa_bisa_mengakses_rute_kepala_desa(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $response = $this->actingAs($kepala)->get('/kepala/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Kepala Desa - placeholder');
    }

    public function test_kepala_desa_tidak_bisa_mengakses_rute_admin_desa(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $response = $this->actingAs($kepala)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_rute_dashboard_utama_mengarahkan_sesuai_peran(): void
    {
        $warga = User::factory()->warga()->create();
        $this->actingAs($warga)->get('/dashboard')->assertRedirect('/warga/dashboard');

        $admin = User::factory()->adminDesa()->create();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin/dashboard');

        $kepala = User::factory()->kepalaDesa()->create();
        $this->actingAs($kepala)->get('/dashboard')->assertRedirect('/kepala/dashboard');
    }
}
