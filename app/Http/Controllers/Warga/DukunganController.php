<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DukunganController extends Controller
{
    /**
     * Beri dukungan pada aspirasi warga lain (PBI #41).
     * Kriteria: satu warga hanya tercatat satu dukungan pada setiap aspirasi.
     */
    public function store(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        abort_unless(Gate::allows('lihatPublik', $pengaduan), 404);
        Gate::authorize('dukung', $pengaduan);

        $pengaduan->dukungan()->createOrFirst(['user_id' => $request->user()->id]);

        return back()->with('success', 'Terima kasih, dukungan Anda telah dicatat.');
    }

    /**
     * Batalkan dukungan yang pernah diberikan (PBI #41).
     */
    public function destroy(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        abort_unless(Gate::allows('lihatPublik', $pengaduan), 404);
        Gate::authorize('dukung', $pengaduan);

        $pengaduan->dukungan()->where('user_id', $request->user()->id)->delete();

        return back()->with('success', 'Dukungan Anda telah dibatalkan.');
    }
}
