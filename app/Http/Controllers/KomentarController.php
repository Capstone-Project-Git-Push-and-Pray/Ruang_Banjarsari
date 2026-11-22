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
     * Tambahkan komentar pada laporan (PBI #33 & PBI #34).
     * Kriteria:
     * - Warga: hanya pemilik laporan yang bisa berkomentar dan harus berstatus aktif.
     * - Admin Desa: dapat membalas komentar warga (balasan berlabel Admin Desa).
     * - Kepala Desa: dapat memberikan catatan supervisi.
     */
    public function store(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = $request->user();

        // Validasi hak akses komentar berdasarkan kepemilikan dan peran (PBI #33)
        if ($user->isWarga()) {
            if (! $user->isAktif() || (int) $pengaduan->user_id !== (int) $user->id) {
                abort(403, 'Hanya warga pemilik laporan yang dapat menambahkan komentar pada laporan ini.');
            }
        } elseif (! $user->isAdminDesa() && ! $user->isKepalaDesa()) {
            abort(403, 'Anda tidak memiliki hak akses untuk memberikan komentar.');
        }

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

        $komentar->delete();

        return back()->with('status', 'komentar-berhasil-dihapus');
    }
}
