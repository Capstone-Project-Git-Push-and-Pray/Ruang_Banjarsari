<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RegistrasiWargaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StatusPendaftaranController extends Controller
{
    /**
     * Tampilkan halaman status pendaftaran warga (Menunggu Tinjauan atau Ditolak).
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAktif()) {
            return redirect()->route('dashboard')
                ->with('info', 'Akun Anda sudah berstatus aktif.');
        }

        return view('warga.status-pendaftaran', compact('user'));
    }

    /**
     * Tangani pengajuan ulang perbaikan data bagi akun yang ditolak.
     */
    public function ajukanUlang(Request $request, RegistrasiWargaService $service): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isDitolak()) {
            return redirect()->route('pendaftaran.status')
                ->with('warning', 'Pengajuan ulang data hanya dapat dilakukan jika pendaftaran akun Anda berstatus ditolak.');
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string'],
            'rt' => ['required', 'string', 'max:3'],
            'rw' => ['required', 'string', 'max:3'],
            'dusun' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
                'required_without:telepon',
            ],
            'telepon' => ['nullable', 'string', 'max:20', 'required_without:email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap sesuai KTP wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'alamat.required' => 'Alamat tempat tinggal wajib diisi.',
            'rt.required' => 'RT wajib diisi.',
            'rw.required' => 'RW wajib diisi.',
            'dusun.required' => 'Dusun tempat tinggal wajib diisi.',
            'email.required_without' => 'Minimal salah satu kontak (Email atau Nomor Telepon) wajib diisi.',
            'telepon.required_without' => 'Minimal salah satu kontak (Email atau Nomor Telepon) wajib diisi.',
            'email.unique' => 'Alamat email ini telah digunakan oleh akun lain.',
        ]);

        $service->ajukanUlang($user, $validated);

        if ($user->isAktif()) {
            return redirect()->route('dashboard')
                ->with('success', 'Data Anda berhasil diperbaiki dan cocok dengan data kependudukan. Akun Anda kini aktif!');
        }

        return redirect()->route('pendaftaran.status')
            ->with('info', 'Data perbaikan berhasil dikirim. Akun Anda saat ini sedang menunggu peninjauan ulang oleh Admin Desa.');
    }
}
