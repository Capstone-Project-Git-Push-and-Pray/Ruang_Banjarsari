<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Tampilkan daftar seluruh kategori pengaduan/aspirasi (PBI #30).
     */
    public function index(): View
    {
        $kategoris = Kategori::withCount('pengaduan')
            ->orderBy('nama')
            ->paginate(15);

        return view('admin.kategori.index', compact('kategoris'));
    }

    /**
     * Tambahkan kategori baru (PBI #30).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'min:3', 'max:100', 'unique:kategori,nama'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.min' => 'Nama kategori minimal terdiri dari 3 karakter.',
            'nama.max' => 'Nama kategori maksimal 100 karakter.',
            'nama.unique' => 'Nama kategori ini sudah terdaftar sebelumnya.',
        ]);

        Kategori::create([
            'nama' => trim($validated['nama']),
            'aktif' => true,
        ]);

        return redirect()->route('admin.kategori.index')
            ->with('status', 'kategori-berhasil-ditambahkan');
    }

    /**
     * Perbarui nama atau status kategori (PBI #30).
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'min:3', 'max:100', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.min' => 'Nama kategori minimal terdiri dari 3 karakter.',
            'nama.max' => 'Nama kategori maksimal 100 karakter.',
            'nama.unique' => 'Nama kategori tersebut sudah digunakan.',
        ]);

        $kategori->update([
            'nama' => trim($validated['nama']),
            'aktif' => $request->boolean('aktif', $kategori->aktif),
        ]);

        return redirect()->route('admin.kategori.index')
            ->with('status', 'kategori-berhasil-diperbarui');
    }

    /**
     * Aktifkan atau nonaktifkan kategori (PBI #30).
     */
    public function toggle(Kategori $kategori): RedirectResponse
    {
        $kategori->update([
            'aktif' => ! $kategori->aktif,
        ]);

        return redirect()->route('admin.kategori.index')
            ->with('status', $kategori->aktif ? 'kategori-diaktifkan' : 'kategori-dinonaktifkan');
    }

    /**
     * Hapus kategori (PBI #30).
     * Kriteria: Kategori yang sudah dipakai pada laporan warga hanya bisa dinonaktifkan (tidak boleh dihapus).
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->pengaduan()->exists()) {
            return back()->with('error', 'Kategori ini telah digunakan dalam laporan warga dan tidak dapat dihapus. Anda hanya dapat menonaktifkannya agar tidak muncul di form baru.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')
            ->with('status', 'kategori-berhasil-dihapus');
    }
}
