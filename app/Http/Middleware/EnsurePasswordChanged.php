<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Rute yang tetap boleh dibuka selama pengguna belum mengganti kata sandi sementara.
     *
     * @var list<string>
     */
    private const RUTE_DIIZINKAN = ['profile.edit', 'password.update', 'logout'];

    /**
     * Arahkan pengguna yang memakai kata sandi sementara dari admin ke halaman ganti kata sandi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->wajib_ganti_password && ! $request->routeIs(...self::RUTE_DIIZINKAN)) {
            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}
