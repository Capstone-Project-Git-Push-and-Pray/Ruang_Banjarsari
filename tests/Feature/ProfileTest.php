<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_admin_profile_page_uses_admin_panel_layout(): void
    {
        $admin = User::factory()->adminDesa()->create();

        $this->actingAs($admin)
            ->get('/profile')
            ->assertOk()
            ->assertSee(['Profil Saya', 'Riwayat Moderasi', 'Admin Desa'])
            ->assertDontSee('Hapus Akun Saya');
    }

    public function test_kepala_desa_profile_page_hides_account_deletion(): void
    {
        $kepala = User::factory()->kepalaDesa()->create();

        $this->actingAs($kepala)
            ->get('/profile')
            ->assertOk()
            ->assertSee(['Ubah Kata Sandi', 'Kepala Desa'])
            ->assertDontSee('Hapus Akun Saya');
    }

    public function test_warga_profile_page_shows_identity_and_account_deletion(): void
    {
        $warga = User::factory()->warga()->create(['nik' => '3207010101900009']);

        $this->actingAs($warga)
            ->get('/profile')
            ->assertOk()
            ->assertSee(['3207010101900009', 'Ubah Kata Sandi', 'Hapus Akun Saya', 'Kembali ke Beranda', route('dashboard')]);
    }

    public function test_profile_information_can_be_updated_by_admin(): void
    {
        $user = User::factory()->adminDesa()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Admin Baru',
                'email' => 'admin-baru@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('ADMIN BARU', $user->name);
        $this->assertSame('admin-baru@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_warga_can_update_email_phone_and_whatsapp(): void
    {
        $user = User::factory()->warga()->create([
            'email' => 'warga.lama@example.com',
            'telepon' => '081111111111',
            'whatsapp' => '081111111111',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'email' => 'warga.baru@example.com',
                'telepon' => '082222222222',
                'whatsapp' => '083333333333',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('warga.baru@example.com', $user->email);
        $this->assertSame('082222222222', $user->telepon);
        $this->assertSame('083333333333', $user->whatsapp);
    }

    public function test_warga_cannot_remove_email_or_phone_from_profile(): void
    {
        $user = User::factory()->warga()->create();

        $this->actingAs($user)
            ->patch('/profile', ['email' => '', 'telepon' => '081234567800'])
            ->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->patch('/profile', ['email' => 'warga@example.com', 'telepon' => ''])
            ->assertSessionHasErrors('telepon');
    }

    public function test_warga_cannot_change_nik_and_nama_lengkap(): void
    {
        $originalNik = '3207010101900001';
        $originalNama = 'BUDI SANTOSO';

        $user = User::factory()->warga()->create([
            'nik' => $originalNik,
            'nama_lengkap' => $originalNama,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'nik' => '3207999999999999',
                'name' => 'NAMA PALSU',
                'nama_lengkap' => 'NAMA PALSU',
                'email' => 'budi.baru@example.com',
                'telepon' => '081234567800',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        // NIK dan Nama Lengkap TIDAK boleh berubah
        $this->assertSame($originalNik, $user->nik);
        $this->assertSame($originalNama, $user->nama_lengkap);
        $this->assertSame('budi.baru@example.com', $user->email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->adminDesa()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_password_is_required_to_delete_account(): void
    {
        $user = User::factory()->warga()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->delete('/profile', ['password' => ''])
            ->assertSessionHasErrorsIn('userDeletion', ['password' => 'Kata sandi wajib diisi.'])
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
