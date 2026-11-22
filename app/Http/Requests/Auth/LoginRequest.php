<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('login', $this->input('email')));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

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

        if (! $attempt) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $login = $this->input('login', $this->input('email'));

        return Str::transliterate(Str::lower($login).'|'.$this->ip());
    }
}
