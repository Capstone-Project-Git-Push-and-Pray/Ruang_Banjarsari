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
     * Status akun warga yang boleh dibantu atur ulang kata sandi.
     * Akun yang pendaftarannya ditolak tidak dilayani.
     */
    private const STATUS_DAPAT_DIRESET = [StatusAkun::Aktif, StatusAkun::MenungguTinjauan];

    /**
     * Tampilkan daftar akun warga yang dapat dibantu pengaturan ulang kata sandinya.
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(StatusAkun::class)->only(self::STATUS_DAPAT_DIRESET)],
        ]);

        $kataKunci = trim($filter['q'] ?? '');

        $wargaList = User::query()
            ->where('role', Role::Warga)
            ->whereIn('status_akun', self::STATUS_DAPAT_DIRESET)
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status_akun', $status))
            ->when($kataKunci !== '', fn ($query) => $query->whereAny(['nama_lengkap', 'nik', 'email'], 'like', "%{$kataKunci}%"))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reset-akun.index', [
            'wargaList' => $wargaList,
            'jumlahMenungguGanti' => User::where('role', Role::Warga)->where('wajib_ganti_password', true)->count(),
            'statusDapatDireset' => self::STATUS_DAPAT_DIRESET,
        ]);
    }

    /**
     * Kirim tautan pengaturan ulang kata sandi ke email warga.
     */
    public function kirimTautan(User $user): RedirectResponse
    {
        abort_unless($this->dapatDireset($user), 404);

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
        abort_unless($this->dapatDireset($user), 404);

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

    /**
     * Hanya akun warga aktif atau yang sedang ditinjau yang dapat dibantu reset.
     */
    private function dapatDireset(User $user): bool
    {
        return $user->isWarga() && in_array($user->status_akun, self::STATUS_DAPAT_DIRESET, true);
    }
}
