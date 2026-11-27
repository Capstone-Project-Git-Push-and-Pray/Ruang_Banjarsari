<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Services\PengingatTindakLanjutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengingatController extends Controller
{
    /**
     * Kirim pengingat tindak lanjut ke seluruh Admin Desa untuk laporan yang belum ditanggapi (PBI #50, #51).
     */
    public function store(Request $request, Pengaduan $pengaduan, PengingatTindakLanjutService $pengingatService): RedirectResponse
    {
        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'catatan.max' => 'Catatan pengingat maksimal 255 karakter.',
        ]);

        $pengingatService->kirim($pengaduan, $request->user(), $validated['catatan'] ?? null);

        return back()->with('success', 'Pengingat untuk laporan "'.$pengaduan->judul.'" telah dikirim ke Admin Desa.');
    }
}
