<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        // Rate limiter untuk proteksi brute force login (5 percobaan / menit per identitas + IP)
        RateLimiter::for('login', function (Request $request) {
            $identifier = (string) ($request->input('email') ?? $request->input('nik') ?? $request->ip());

            return Limit::perMinute(5)->by($identifier.'|'.$request->ip());
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
