<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DukunganController extends Controller
{
    /**
     * Beri dukungan pada aspirasi warga lain (PBI #41).
     * Kriteria: satu warga hanya tercatat satu dukungan pada setiap aspirasi.
     */
    public function store(Request $request, Pengaduan $pengaduan): JsonResponse|RedirectResponse
    {
        abort_unless(Gate::allows('lihatPublik', $pengaduan), 404);
        Gate::authorize('dukung', $pengaduan);

        $pengaduan->dukungan()->createOrFirst(['user_id' => $request->user()->id]);

        return $this->tanggapan($request, $pengaduan);
    }

    /**
     * Batalkan dukungan yang pernah diberikan (PBI #41).
     */
    public function destroy(Request $request, Pengaduan $pengaduan): JsonResponse|RedirectResponse
    {
        abort_unless(Gate::allows('lihatPublik', $pengaduan), 404);
        Gate::authorize('dukung', $pengaduan);

        $pengaduan->dukungan()->where('user_id', $request->user()->id)->delete();

        return $this->tanggapan($request, $pengaduan);
    }

    /**
     * Kembalikan status dukungan terbaru tanpa memuat ulang halaman, seperti tombol suka di media sosial.
     * Tanpa JavaScript, kembali ke halaman sebelumnya tanpa pesan agar posisi baca tidak terganggu.
     */
    private function tanggapan(Request $request, Pengaduan $pengaduan): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return back();
        }

        return response()->json([
            'didukung' => $pengaduan->dukungan()->where('user_id', $request->user()->id)->exists(),
            'jumlah' => $pengaduan->dukungan()->count(),
        ]);
    }
}
