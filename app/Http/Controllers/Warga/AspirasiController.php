<?php

namespace App\Http\Controllers\Warga;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AspirasiController extends Controller
{
    /**
     * Tampilkan daftar aspirasi publik seluruh warga (PBI #39).
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(array_column(StatusLaporan::aspirasiCases(), 'value'))],
            'urut' => ['nullable', Rule::in(['terbaru', 'dukungan'])],
        ]);

        $kataKunci = trim($filter['q'] ?? '');

        $aspirasis = Pengaduan::query()
            ->aspirasiPublik()
            ->with(['user', 'kategori', 'lampiran'])
            ->withCount(['dukungan', 'komentar' => fn ($query) => $query->tampil()])
            ->withExists(['dukungan as didukung' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->when($filter['kategori_id'] ?? null, fn ($query, $kategoriId) => $query->where('kategori_id', $kategoriId))
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($kataKunci !== '', fn ($query) => $query->whereAny(['judul', 'deskripsi', 'lokasi'], 'like', "%{$kataKunci}%"))
            ->when(($filter['urut'] ?? null) === 'dukungan', fn ($query) => $query->orderByDesc('dukungan_count'))
            ->latest()
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('warga.aspirasi.index', [
            'aspirasis' => $aspirasis,
            'kategoriList' => Kategori::orderBy('nama')->get(),
            'statusList' => StatusLaporan::aspirasiCases(),
        ]);
    }

    /**
     * Tampilkan detail aspirasi publik (PBI #40).
     * Pengaduan dan aspirasi yang disembunyikan diperlakukan seolah tidak ada (404).
     */
    public function show(Request $request, Pengaduan $pengaduan): View
    {
        abort_unless(Gate::allows('lihatPublik', $pengaduan), 404);

        $user = $request->user();

        return view('warga.aspirasi.show', [
            'pengaduan' => $pengaduan
                ->load([
                    'user',
                    'kategori',
                    'lampiran',
                    'komentar' => fn ($query) => $query->tampilUntuk($user)->with('user'),
                ])
                ->loadCount('dukungan')
                ->loadExists(['dukungan as didukung' => fn ($query) => $query->where('user_id', $user->id)]),
        ]);
    }
}
