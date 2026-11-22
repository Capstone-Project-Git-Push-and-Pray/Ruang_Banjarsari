<?php

namespace App\Providers;

use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Policies\KomentarPolicy;
use App\Policies\PengaduanPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan kebijakan otorisasi model (PBI #22)
        Gate::policy(Pengaduan::class, PengaduanPolicy::class);
        Gate::policy(Komentar::class, KomentarPolicy::class);

        // Rate limiter untuk proteksi brute force login (5 percobaan / menit per identitas + IP)
        RateLimiter::for('login', function (Request $request) {
            $identifier = (string) ($request->input('login') ?? $request->input('email') ?? $request->input('nik') ?? $request->ip());

            return Limit::perMinute(5)->by(Str::lower($identifier).'|'.$request->ip());
        });

        // Rate limiter untuk formulir registrasi (5 per menit per IP)
        RateLimiter::for('registrasi', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiter untuk permintaan reset password (3 per menit per IP)
        RateLimiter::for('reset-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
