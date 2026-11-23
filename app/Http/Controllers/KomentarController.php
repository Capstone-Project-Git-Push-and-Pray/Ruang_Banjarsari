<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use App\Models\Pengaduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KomentarController extends Controller
{
    /**
     * Tambahkan komentar pada laporan (PBI #33, PBI #34 & PBI #42).
     * Kriteria:
     * - Pengaduan: hanya warga aktif pemilik laporan yang bisa berkomentar.
     * - Aspirasi: seluruh warga aktif bisa berkomentar selama tidak disembunyikan.
     * - Admin Desa: dapat membalas komentar warga (balasan berlabel Admin Desa).
     * - Kepala Desa: dapat memberikan catatan supervisi.
     */
    public function store(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = $request->user();

        // Validasi hak akses komentar berdasarkan kepemilikan, peran, dan visibilitas (PBI #33, PBI #42)
        Gate::authorize('komentari', $pengaduan);

        $validated = $request->validate([
            'isi' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'isi.required' => 'Isi komentar atau tanggapan wajib diisi.',
            'isi.min' => 'Isi komentar minimal terdiri dari 2 karakter.',
            'isi.max' => 'Isi komentar maksimal 2000 karakter.',
        ]);

        Komentar::create([
            'pengaduan_id' => $pengaduan->id,
            'user_id' => $user->id,
            'isi' => trim($validated['isi']),
        ]);

        return back()->with('status', 'komentar-berhasil-ditambahkan');
    }

    /**
     * Perbarui isi komentar (PBI #34).
     * Kriteria: Komentar tidak dapat diubah oleh orang lain (hanya penulis yang boleh mengubah).
     */
    public function update(Request $request, Komentar $komentar): RedirectResponse
    {
        Gate::authorize('update', $komentar);

        $validated = $request->validate([
            'isi' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'isi.required' => 'Isi komentar wajib diisi.',
            'isi.min' => 'Isi komentar minimal 2 karakter.',
            'isi.max' => 'Isi komentar maksimal 2000 karakter.',
        ]);

        $komentar->update([
            'isi' => trim($validated['isi']),
        ]);

        return back()->with('status', 'komentar-berhasil-diperbarui');
    }

    /**
     * Hapus komentar (PBI #34).
     */
    public function destroy(Komentar $komentar): RedirectResponse
    {
        Gate::authorize('delete', $komentar);

        // Penghapusan oleh penulis bersifat permanen; soft delete khusus keputusan moderasi
        $komentar->forceDelete();

        return back()->with('status', 'komentar-berhasil-dihapus');
    }
}
