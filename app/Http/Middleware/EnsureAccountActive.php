<?php

namespace App\Http\Middleware;

use App\Enums\StatusAkun;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Memastikan akun pengguna berstatus aktif sebelum dapat mengakses fitur tertentu.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAktif()) {
            $pesan = match ($user->status_akun) {
                StatusAkun::MenungguTinjauan => 'Akun Anda sedang dalam proses peninjauan oleh Admin Desa.',
                StatusAkun::Ditolak => 'Akun Anda telah ditolak: '.($user->alasan_penolakan ?? 'Silakan hubungi Admin Desa.'),
                default => 'Akun Anda tidak aktif.',
            };

            return redirect()->route('landing')->with('error', $pesan);
        }

        return $next($request);
    }
}
