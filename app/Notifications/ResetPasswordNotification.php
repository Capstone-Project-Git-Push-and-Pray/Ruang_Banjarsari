<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

/**
 * Email tautan atur ulang kata sandi berbahasa Indonesia dengan tema SILAFA Banjarsari.
 */
class ResetPasswordNotification extends ResetPassword
{
    /**
     * Bangun pesan email atur ulang kata sandi.
     */
    public function toMail($notifiable): MailMessage
    {
        $broker = config('auth.defaults.passwords');

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi Akun SILAFA Banjarsari')
            ->view('emails.reset-password', [
                'namaPenerima' => $notifiable instanceof User && $notifiable->nama_lengkap ? Str::title(Str::lower($notifiable->nama_lengkap)) : null,
                'tautanReset' => $this->resetUrl($notifiable),
                'menitKedaluwarsa' => (int) config("auth.passwords.{$broker}.expire", 60),
            ]);
    }
}
