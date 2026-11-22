<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan halaman permintaan tautan atur ulang kata sandi.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Tangani pengiriman permintaan tautan reset kata sandi ke email.
     * Mengembalikan respons yang seragam (PBI #20) untuk mencegah user enumeration.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
        ]);

        Password::sendResetLink(
            $request->only('email')
        );

        return back()->with('status', 'Jika alamat email Anda terdaftar, tautan pengaturan ulang kata sandi telah dikirimkan.');
    }
}
