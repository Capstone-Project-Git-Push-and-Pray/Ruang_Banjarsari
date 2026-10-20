<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TODO PBI #6: Rute registrasi mandiri warga dengan validasi NIK kependudukan desa.
     */
    public function test_registration_screen_is_temporarily_disabled_pending_pbi_6(): void
    {
        // Rute registrasi bawaan dinonaktifkan sementara sesuai spesifikasi PBI #1
        $response = $this->get('/register');

        $response->assertStatus(404);
    }
}
