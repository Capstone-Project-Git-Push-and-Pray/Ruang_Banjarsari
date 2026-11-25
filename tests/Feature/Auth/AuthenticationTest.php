<?php

namespace Tests\Feature\Auth;

use App\Enums\StatusAkun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Anda');
    }

    public function test_warga_can_authenticate_using_nik_and_redirected_to_dashboard(): void
    {
        $warga = User::factory()->warga()->create([
            'nik' => '3207011205900001',
            'password' => bcrypt('password123'),
            'status_akun' => StatusAkun::Aktif,
        ]);

        $response = $this->post('/login', [
            'login' => '3207011205900001',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($warga);
        $response->assertRedirect(route('dashboard'));

        // Akses dashboard mengarahkan ke dashboard warga
        $dashboardResponse = $this->actingAs($warga)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('warga.dashboard'));
    }

    public function test_warga_menunggu_tinjauan_redirected_to_status_pendaftaran(): void
    {
        $wargaPending = User::factory()->warga()->menungguTinjauan()->create([
            'nik' => '3207011205900002',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => '3207011205900002',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($wargaPending);
        $response->assertRedirect(route('dashboard'));

        // Dashboard me-redirect ke status pendaftaran
        $dashboardResponse = $this->actingAs($wargaPending)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('pendaftaran.status'));
    }

    public function test_admin_desa_can_authenticate_using_email_or_username(): void
    {
        $admin = User::factory()->adminDesa()->create([
            'email' => 'admin@desa-banjarsari.test',
            'password' => bcrypt('password123'),
        ]);

        // Login dengan Email
        $responseEmail = $this->post('/login', [
            'login' => 'admin@desa-banjarsari.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $responseEmail->assertRedirect(route('dashboard'));

        $dashboardResponse = $this->actingAs($admin)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        // Login dengan Username ("admin")
        $responseUsername = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $responseUsername->assertRedirect(route('dashboard'));
    }

    public function test_kepala_desa_can_authenticate_using_username_and_redirected(): void
    {
        $kades = User::factory()->kepalaDesa()->create([
            'email' => 'kades@desa-banjarsari.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'kades',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($kades);
        $response->assertRedirect(route('dashboard'));

        $dashboardResponse = $this->actingAs($kades)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('kepala.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_failed_login_shows_readable_indonesian_message(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'login' => 'NIK/username atau kata sandi salah.',
        ]);
        $this->assertGuest();
    }

    public function test_failed_login_flashes_remaining_attempts(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'salah'])
            ->assertSessionHas('login_sisa_percobaan', 2);

        $this->post('/login', ['login' => $user->email, 'password' => 'salah'])
            ->assertSessionHas('login_sisa_percobaan', 1);
    }

    public function test_account_is_suspended_after_three_failures_even_with_correct_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password-benar')]);

        $this->gagalLogin($user->email, 3);

        $response = $this->post('/login', ['login' => $user->email, 'password' => 'password-benar']);

        $response->assertSessionHas('login_tertangguh_sampai');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_suspension_follows_the_account_not_the_ip_address(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password-benar')]);

        $this->gagalLogin($user->email, 3);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->post('/login', ['login' => $user->email, 'password' => 'password-benar']);

        $this->assertGuest();
    }

    public function test_suspension_is_shared_between_email_and_username_of_same_account(): void
    {
        $user = User::factory()->adminDesa()->create([
            'email' => 'operator@desa-banjarsari.test',
            'password' => bcrypt('password-benar'),
        ]);

        $this->gagalLogin('operator', 3);

        $this->post('/login', ['login' => $user->email, 'password' => 'password-benar']);

        $this->assertGuest();
    }

    public function test_suspension_duration_escalates_and_resets_after_successful_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password-benar')]);

        // Penangguhan pertama: 5 menit
        $this->gagalLogin($user->email, 3);
        $this->travel(5)->minutes();
        $this->travel(1)->seconds();

        // Penangguhan kedua: 15 menit, sehingga 5 menit kemudian masih terkunci
        $this->gagalLogin($user->email, 3);
        $this->travel(6)->minutes();
        $this->post('/login', ['login' => $user->email, 'password' => 'password-benar']);
        $this->assertGuest();

        // Setelah 15 menit berlalu, login benar berhasil dan riwayat direset
        $this->travel(10)->minutes();
        $this->post('/login', ['login' => $user->email, 'password' => 'password-benar']);
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->post('/login', ['login' => $user->email, 'password' => 'salah'])
            ->assertSessionHas('login_sisa_percobaan', 2);
    }

    public function test_login_page_shows_suspension_countdown(): void
    {
        $user = User::factory()->create();

        $this->gagalLogin($user->email, 3);

        $this->followingRedirects()
            ->from('/login')
            ->post('/login', ['login' => $user->email, 'password' => 'salah'])
            ->assertSee('Akun ditangguhkan sementara.');
    }

    private function gagalLogin(string $login, int $kali): void
    {
        for ($i = 0; $i < $kali; $i++) {
            $this->post('/login', ['login' => $login, 'password' => 'password-salah']);
        }
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_kades_login_clears_admin_intended_url_and_redirects_to_dashboard(): void
    {
        $kades = User::factory()->kepalaDesa()->create([
            'email' => 'kades@desa-banjarsari.test',
            'password' => bcrypt('password'),
        ]);

        // Simulasikan session memiliki intended URL ke /admin/dashboard
        $response = $this->withSession(['url.intended' => 'http://localhost/admin/dashboard'])
            ->post('/login', [
                'login' => 'kades@desa-banjarsari.test',
                'password' => 'password',
            ]);

        $this->assertAuthenticatedAs($kades);
        // Memastikan tidak diredirect ke /admin/dashboard melainkan ke /dashboard
        $response->assertRedirect(route('dashboard'));

        $dashboardResponse = $this->actingAs($kades)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('kepala.dashboard'));
    }
}
