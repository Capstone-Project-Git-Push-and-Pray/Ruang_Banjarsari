<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\PenguncianLoginService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('login') && $this->filled('email')) {
            $this->merge(['login' => $this->input('email')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get the custom validation messages for the login form.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'NIK, username, atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $penguncian = app(PenguncianLoginService::class);

        $login = trim((string) $this->input('login', $this->input('email')));
        $password = (string) $this->input('password');
        $pengguna = $this->cariPengguna($login);
        $identitas = $this->identitasPenangguhan($pengguna, $login);

        // "Ingat saya" hanya untuk warga, berlaku sesuai config/sesi.php (bawaan 30 hari)
        $remember = $this->boolean('remember') && $pengguna?->bolehIngatSaya() === true;
        Auth::guard('web')->setRememberDuration((int) config('sesi.ingat_saya_hari') * 24 * 60);

        $sisaDetik = $penguncian->sisaDetikTertangguh($identitas);

        if ($sisaDetik > 0) {
            $this->tolakKarenaTertangguh($sisaDetik);
        }

        $attempt = false;

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $attempt = Auth::attempt(['email' => $login, 'password' => $password], $remember);
        } elseif (preg_match('/^[0-9]{16}$/', $login)) {
            $attempt = Auth::attempt(['nik' => $login, 'password' => $password], $remember);
        } else {
            // Coba NIK langsung atau Email
            $attempt = Auth::attempt(['nik' => $login, 'password' => $password], $remember)
                || Auth::attempt(['email' => $login, 'password' => $password], $remember);

            // Jika belum berhasil dan berupa username (tanpa @), cocokkan dengan awalan email admin/kades
            if (! $attempt) {
                $user = User::where('email', 'like', $login.'@%')->first();
                if ($user && Hash::check($password, $user->password)) {
                    Auth::login($user, $remember);
                    $attempt = true;
                }
            }
        }

        if ($attempt) {
            $penguncian->bersihkan($identitas);

            return;
        }

        $hasil = $penguncian->catatGagal($identitas);

        if ($hasil['detik_tertangguh'] > 0) {
            $this->tolakKarenaTertangguh($hasil['detik_tertangguh']);
        }

        $this->session()->flash('login_sisa_percobaan', $hasil['sisa_percobaan']);

        throw ValidationException::withMessages([
            'login' => 'NIK/username atau kata sandi salah.',
        ]);
    }

    /**
     * Cari akun berdasarkan NIK, email, atau username (awalan email admin/kades).
     */
    private function cariPengguna(string $login): ?User
    {
        if ($login === '') {
            return null;
        }

        $user = User::query()
            ->where('nik', $login)
            ->orWhere('email', mb_strtolower($login))
            ->first();

        if ($user === null && ! str_contains($login, '@')) {
            $user = User::where('email', 'like', $login.'@%')->first();
        }

        return $user;
    }

    /**
     * Identitas penangguhan per akun: ID pengguna bila akun ditemukan,
     * sehingga NIK, email, dan username milik akun yang sama berbagi satu hitungan.
     */
    private function identitasPenangguhan(?User $user, string $login): string
    {
        return $user !== null ? 'user:'.$user->getKey() : 'login:'.mb_strtolower($login);
    }

    /**
     * Tolak login karena akun sedang ditangguhkan sementara.
     *
     * @throws ValidationException
     */
    private function tolakKarenaTertangguh(int $sisaDetik): never
    {
        $this->session()->flash('login_tertangguh_sampai', now()->getTimestamp() + $sisaDetik);

        throw ValidationException::withMessages([
            'login' => 'Akun ditangguhkan sementara karena terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam '.ceil($sisaDetik / 60).' menit.',
        ]);
    }
}
