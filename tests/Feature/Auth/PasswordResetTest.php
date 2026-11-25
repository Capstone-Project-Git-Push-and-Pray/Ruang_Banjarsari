<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Kata Sandi');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $response->assertSessionHas('status');
    }

    public function test_response_is_identical_whether_email_is_registered_or_not(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'terdaftar@desa-banjarsari.test']);

        // 1. Email Terdaftar
        $responseRegistered = $this->post('/forgot-password', ['email' => 'terdaftar@desa-banjarsari.test']);
        $responseRegistered->assertRedirect();
        $responseRegistered->assertSessionHasNoErrors();
        $responseRegistered->assertSessionHas('status', 'Jika alamat email Anda terdaftar, tautan pengaturan ulang kata sandi telah dikirimkan.');

        // 2. Email Tidak Terdaftar (PBI #20: respons seragam mencegah user enumeration)
        $responseUnregistered = $this->post('/forgot-password', ['email' => 'tidak.ada@desa-banjarsari.test']);
        $responseUnregistered->assertRedirect();
        $responseUnregistered->assertSessionHasNoErrors();
        $responseUnregistered->assertSessionHas('status', 'Jika alamat email Anda terdaftar, tautan pengaturan ulang kata sandi telah dikirimkan.');
    }

    public function test_reset_password_email_is_in_indonesian_with_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            $this->assertSame('Atur Ulang Kata Sandi Akun SILAFA Banjarsari', $mail->subject);
            $this->assertSame('emails.reset-password', $mail->view);
            $this->assertStringContainsString('/reset-password/'.$notification->token, $mail->viewData['tautanReset']);

            return true;
        });
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'))
                ->assertSessionHas('status', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru Anda.');

            return true;
        });
    }

    public function test_reset_password_token_cannot_be_reused(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            // Pemakaian pertama token -> Berhasil
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])->assertSessionHasNoErrors();

            // Pemakaian kedua token yang sama -> Gagal (PBI #20: tautan sekali pakai)
            $responseSecond = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password-2',
                'password_confirmation' => 'new-password-2',
            ]);

            $responseSecond->assertSessionHasErrors([
                'email' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            ]);

            return true;
        });
    }
}
