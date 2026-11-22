<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Http\Controllers\Controller;
use App\Models\DataKependudukan;
use App\Models\User;
use App\Services\RegistrasiWargaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeninjauanPendaftaranController extends Controller
{
    /**
     * Tampilkan daftar pendaftaran akun warga yang berstatus Menunggu Tinjauan.
     * Terurut dari pendaftar terlama (PBI #16).
     */
    public function index(): View
    {
        $pendaftarList = User::where('role', Role::Warga)
            ->where('status_akun', StatusAkun::MenungguTinjauan)
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('admin.pendaftaran.index', compact('pendaftarList'));
    }

    /**
     * Tampilkan halaman perbandingan detail data pendaftar dan master data kependudukan (PBI #17).
     */
    public function show(User $user, RegistrasiWargaService $service): View
    {
        abort_unless($user->role === Role::Warga, 404);

        $kependudukan = DataKependudukan::where('nik', $user->nik)->first();
        $perbandingan = $service->bandingkanData($user, $kependudukan);

        return view('admin.pendaftaran.show', compact('user', 'kependudukan', 'perbandingan'));
    }

    /**
     * Tangani persetujuan pendaftaran warga (PBI #18: Status -> Aktif).
     */
    public function setujui(Request $request, User $user, RegistrasiWargaService $service): RedirectResponse
    {
        abort_unless($user->role === Role::Warga, 404);

        $service->setujuiPendaftaran($user, $request->user());

        return redirect()->route('admin.pendaftaran.index')
            ->with('success', "Pendaftaran warga {$user->nama_lengkap} (NIK: {$user->nik}) berhasil disetujui. Akun kini aktif.");
    }

    /**
     * Tangani penolakan pendaftaran warga beserta alasannya (PBI #18: Status -> Ditolak).
     */
    public function tolak(Request $request, User $user, RegistrasiWargaService $service): RedirectResponse
    {
        abort_unless($user->role === Role::Warga, 404);

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi agar warga mengetahui perbaikan yang diperlukan.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal harus 5 karakter.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $service->tolakPendaftaran($user, $request->user(), $validated['alasan_penolakan']);

        return redirect()->route('admin.pendaftaran.index')
            ->with('success', "Pendaftaran warga {$user->nama_lengkap} (NIK: {$user->nik}) telah ditolak.");
    }
}
