<?php

use App\Http\Middleware\BatasiSesiTidakAktif;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan header keamanan global pada web group
        $middleware->web(append: [
            SecurityHeaders::class,
            BatasiSesiTidakAktif::class,
            EnsurePasswordChanged::class,
        ]);

        // Alias middleware untuk otorisasi peran dan status akun
        $middleware->alias([
            'role' => EnsureRole::class,
            'akun.aktif' => EnsureAccountActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Ganti halaman "419 Page Expired" (token formulir kedaluwarsa karena sesi berakhir)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null;
            }

            if ($request->user() === null) {
                return redirect()->route('login')->with('sesi_berakhir', true);
            }

            return redirect()->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation', 'current_password']))
                ->with('error', 'Halaman sudah terlalu lama dibuka. Silakan kirim ulang formulir Anda.');
        });
    })->create();
