<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Memastikan akun pengguna berstatus aktif sebelum dapat mengakses fitur tertentu (misalnya pengajuan laporan).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAktif()) {
            return redirect()->route('pendaftaran.status')
                ->with('warning', 'Pengajuan laporan terkunci selama akun Anda belum aktif.');
        }

        return $next($request);
    }
}
