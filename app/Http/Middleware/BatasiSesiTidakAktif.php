<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akhiri sesi pengguna yang tidak aktif melebihi batas waktu perannya
 * (warga 120 menit, admin dan kepala desa 30 menit; lihat config/sesi.php).
 */
class BatasiSesiTidakAktif
{
    /**
     * Kunci sesi penyimpan waktu aktivitas terakhir (timestamp detik).
     */
    public const KUNCI_AKTIVITAS = 'sesi.aktivitas_terakhir';

    /**
     * Header penanda permintaan latar belakang (mis. pembaruan log dashboard)
     * yang tidak boleh dihitung sebagai aktivitas pengguna.
     */
    public const HEADER_PEMBARUAN_OTOMATIS = 'X-Pembaruan-Otomatis';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $terakhir = $request->session()->get(self::KUNCI_AKTIVITAS);
        $batasDetik = $user->batasTidakAktifMenit() * 60;

        if ($terakhir !== null
            && ! $this->diingatDiPerangkat($request, $user)
            && now()->getTimestamp() - (int) $terakhir > $batasDetik) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi Anda telah berakhir.'], 401);
            }

            return redirect()->route('login')->with('sesi_berakhir', true);
        }

        if (! $request->headers->has(self::HEADER_PEMBARUAN_OTOMATIS)) {
            $request->session()->put(self::KUNCI_AKTIVITAS, now()->getTimestamp());
        }

        return $next($request);
    }

    /**
     * Warga yang memilih "Ingat saya" tetap masuk walaupun lama tidak aktif.
     */
    private function diingatDiPerangkat(Request $request, User $user): bool
    {
        return $user->bolehIngatSaya()
            && $request->cookies->has(Auth::guard('web')->getRecallerName());
    }
}
