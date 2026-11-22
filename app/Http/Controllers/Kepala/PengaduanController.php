<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PengaduanController extends Controller
{
    /**
     * Tampilkan monitoring seluruh laporan pengaduan & aspirasi desa bagi Kepala Desa.
     */
    public function index(Request $request): View
    {
        $pengaduans = Pengaduan::with(['user', 'kategori'])
            ->latest()
            ->paginate(15);

        return view('kepala.pengaduan.index', compact('pengaduans'));
    }

    /**
     * Tampilkan detail laporan pengaduan untuk supervisi Kepala Desa.
     * Kriteria PBI #22: Data pelapor dapat dilihat oleh Kepala Desa.
     */
    public function show(Pengaduan $pengaduan): View
    {
        Gate::authorize('view', $pengaduan);

        return view('kepala.pengaduan.show', [
            'pengaduan' => $pengaduan->load([
                'user',
                'kategori',
                'lampiran',
                'riwayatStatus.diubahOleh',
                'komentar.user',
            ]),
        ]);
    }
}
