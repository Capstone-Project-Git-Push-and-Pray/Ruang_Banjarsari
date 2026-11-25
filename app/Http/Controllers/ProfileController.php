<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan formulir pengaturan profil pengguna.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Perbarui data profil dan kontak pengguna (PBI #21).
     * Kriteria: NIK dan nama tidak dapat diubah sendiri oleh Warga.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // NIK dan Nama Lengkap terkunci dan tidak dapat diubah mandiri oleh Warga
        if ($user->isWarga()) {
            unset($validated['nik'], $validated['name'], $validated['nama_lengkap']);
        } else {
            if (isset($validated['name']) && ! isset($validated['nama_lengkap'])) {
                $validated['nama_lengkap'] = $validated['name'];
            }
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Kata sandi wajib diisi.',
            'password.current_password' => 'Kata sandi tidak sesuai.',
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
