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
