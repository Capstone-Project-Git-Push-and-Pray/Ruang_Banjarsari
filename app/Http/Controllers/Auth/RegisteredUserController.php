<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RegistrasiWargaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran warga baru.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Tangani pengiriman data formulir pendaftaran warga baru.
     */
    public function store(Request $request, RegistrasiWargaService $service): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^[0-9]{16}$/', 'unique:'.User::class.',nik'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string'],
            'rt' => ['required', 'string', 'max:3'],
            'rw' => ['required', 'string', 'max:3'],
            'dusun' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email', 'required_without:telepon'],
            'telepon' => ['nullable', 'string', 'max:20', 'required_without:email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
            'persetujuan' => ['accepted'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap sesuai KTP wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus berjumlah 16 digit.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'alamat.required' => 'Alamat lengkap KTP wajib diisi.',
            'rt.required' => 'RT wajib diisi.',
            'rw.required' => 'RW wajib diisi.',
            'dusun.required' => 'Dusun wajib diisi.',
            'email.required_without' => 'Minimal salah satu kontak (Email atau Nomor Telepon) wajib diisi.',
            'telepon.required_without' => 'Minimal salah satu kontak (Email atau Nomor Telepon) wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'persetujuan.accepted' => 'Anda wajib menyetujui kebijakan privasi dan ketentuan yang berlaku.',
        ]);

        $user = $service->daftarkan($validated);

        event(new Registered($user));

        Auth::login($user);

        if ($user->isAktif()) {
            return redirect()->route('dashboard')
                ->with('success', 'Pendaftaran berhasil! Seluruh data Anda sesuai dengan master data kependudukan desa, akun Anda langsung aktif tanpa perlu persetujuan admin.');
        }

        return redirect()->route('pendaftaran.status')
            ->with('info', 'Terdapat data Anda yang berbeda atau belum sesuai dengan data kependudukan (mastersheet) desa. Akun Anda telah didaftarkan dan berstatus Menunggu Tinjauan oleh Admin Desa.');
    }
}
