<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisLaporan;
use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Tandai satu notifikasi sudah dibaca lalu buka laporan yang diingatkan (PBI #53).
     * Menandai dibaca tidak menyelesaikan pengingat; pengingat selesai saat status laporan berubah (PBI #54).
     */
    public function buka(Request $request, string $notifikasi): RedirectResponse
    {
        $notifikasiAdmin = $request->user()->notifications()->findOrFail($notifikasi);
        $notifikasiAdmin->markAsRead();

        $laporan = Pengaduan::find($notifikasiAdmin->data['pengaduan_id'] ?? null);

        if ($laporan === null) {
            return back()->with('error', 'Laporan pada pengingat ini sudah tidak tersedia.');
        }

        return redirect()->route(
            $laporan->jenis === JenisLaporan::Aspirasi ? 'admin.aspirasi.show' : 'admin.pengaduan.show',
            $laporan,
        );
    }

    /**
     * Tandai seluruh notifikasi milik admin sudah dibaca (PBI #53).
     */
    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
