<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResetAkunWargaController extends Controller
{
    /**
     * Tampilkan daftar akun warga yang dapat dibantu pengaturan ulang kata sandinya.
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(StatusAkun::class)],
        ]);

        $kataKunci = trim($filter['q'] ?? '');

        $wargaList = User::query()
            ->where('role', Role::Warga)
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status_akun', $status))
            ->when($kataKunci !== '', fn ($query) => $query->whereAny(['nama_lengkap', 'nik', 'email'], 'like', "%{$kataKunci}%"))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reset-akun.index', [
            'wargaList' => $wargaList,
            'jumlahMenungguGanti' => User::where('role', Role::Warga)->where('wajib_ganti_password', true)->count(),
        ]);
    }

    /**
     * Kirim tautan pengaturan ulang kata sandi ke email warga.
     */
    public function kirimTautan(User $user): RedirectResponse
    {
        abort_unless($user->isWarga(), 404);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', "Tautan untuk {$user->nama_lengkap} belum dapat dikirim. Tunggu beberapa saat lalu coba lagi.");
        }

        return back()->with('success', "Tautan atur ulang kata sandi telah dikirim ke {$user->email}.");
    }

    /**
     * Buat kata sandi sementara yang wajib diganti warga saat masuk berikutnya.
     */
    public function kataSandiSementara(User $user): RedirectResponse
    {
        abort_unless($user->isWarga(), 404);

        $kataSandiSementara = Str::password(10, symbols: false);

        $user->forceFill([
            'password' => $kataSandiSementara,
            'remember_token' => Str::random(60),
            'wajib_ganti_password' => true,
        ])->save();

        return back()
            ->with('success', "Kata sandi sementara untuk {$user->nama_lengkap} berhasil dibuat.")
            ->with('kata_sandi_sementara', [
                'user_id' => $user->id,
                'nama' => $user->nama_lengkap,
                'kata_sandi' => $kataSandiSementara,
            ]);
    }
}
