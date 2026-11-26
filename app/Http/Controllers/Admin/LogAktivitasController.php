<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AktivitasPetugasService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    /**
     * Tampilkan log lengkap aktivitas petugas, dipisah per jenis dan dapat difilter.
     */
    public function index(Request $request, AktivitasPetugasService $aktivitasPetugas): View
    {
        $filter = $request->validate([
            'jenis' => ['nullable', Rule::in(array_keys(AktivitasPetugasService::daftarJenis()))],
            'petugas' => ['nullable', 'integer'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Tanggal awal tidak valid.',
            'tanggal_selesai.date' => 'Tanggal akhir tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $jenisAktif = $filter['jenis'] ?? null;
        $filterLanjutan = collect($filter)->except('jenis')->all();

        return view('admin.log-aktivitas.index', [
            'logAktivitas' => $aktivitasPetugas->daftar($filter),
            'jumlahPerJenis' => $aktivitasPetugas->jumlahPerJenis($filterLanjutan),
            'daftarJenis' => AktivitasPetugasService::daftarJenis(),
            'daftarPetugas' => User::query()
                ->whereIn('role', [Role::AdminDesa, Role::KepalaDesa])
                ->orderBy('nama_lengkap')
                ->pluck('nama_lengkap', 'id'),
            'jenisAktif' => $jenisAktif,
            'adaFilter' => collect($filterLanjutan)->filter()->isNotEmpty(),
        ]);
    }
}
