<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Http\Controllers\Controller;
use App\Models\DataKependudukan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DataKependudukanController extends Controller
{
    /**
     * Tampilkan halaman daftar master data kependudukan dengan fitur pencarian dan filter.
     */
    public function index(Request $request): View
    {
        $query = DataKependudukan::query()->with('user');

        // Filter pencarian berdasarkan nama lengkap atau NIK atau alamat
        if ($search = $request->filled('search') ? trim((string) $request->input('search')) : null) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('dusun', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan Dusun
        if ($dusun = $request->filled('dusun') ? trim((string) $request->input('dusun')) : null) {
            $query->where('dusun', $dusun);
        }

        // Filter status kepemilikan akun SILAFA
        if ($statusAkun = $request->filled('status_akun') ? (string) $request->input('status_akun') : null) {
            if ($statusAkun === 'aktif') {
                $query->whereHas('user', function ($q) {
                    $q->where('status_akun', StatusAkun::Aktif);
                });
            } elseif ($statusAkun === 'menunggu') {
                $query->whereHas('user', function ($q) {
                    $q->where('status_akun', StatusAkun::MenungguTinjauan);
                });
            } elseif ($statusAkun === 'ditolak') {
                $query->whereHas('user', function ($q) {
                    $q->where('status_akun', StatusAkun::Ditolak);
                });
            } elseif ($statusAkun === 'belum') {
                $query->doesntHave('user');
            }
        }

        // Urutkan data
        $pendudukList = $query->orderBy('nama_lengkap', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Statistik ringkasan data kependudukan untuk kartu metrik
        $totalPenduduk = DataKependudukan::count();
        $totalAkunAktif = User::where('role', Role::Warga)
            ->where('status_akun', StatusAkun::Aktif)
            ->count();
        $totalMenungguTinjauan = User::where('role', Role::Warga)
            ->where('status_akun', StatusAkun::MenungguTinjauan)
            ->count();
        $dusunList = DataKependudukan::whereNotNull('dusun')
            ->where('dusun', '!=', '')
            ->distinct()
            ->orderBy('dusun')
            ->pluck('dusun');

        return view('admin.kependudukan.index', compact(
            'pendudukList',
            'totalPenduduk',
            'totalAkunAktif',
            'totalMenungguTinjauan',
            'dusunList'
        ));
    }

    /**
     * Simpan data kependudukan baru ke master data desa.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:data_kependudukan,nik'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string', 'max:500'],
            'rt' => ['required', 'string', 'max:3'],
            'rw' => ['required', 'string', 'max:3'],
            'dusun' => ['required', 'string', 'max:255'],
        ], [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam master data kependudukan.',
            'nama_lengkap.required' => 'Nama lengkap warga wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'alamat.required' => 'Alamat domisili wajib diisi.',
            'rt.required' => 'RT wajib diisi.',
            'rw.required' => 'RW wajib diisi.',
            'dusun.required' => 'Nama dusun wajib diisi.',
        ]);

        $validated['nama_lengkap'] = Str::of($validated['nama_lengkap'])->squish()->upper()->toString();
        $validated['rt'] = str_pad($validated['rt'], 3, '0', STR_PAD_LEFT);
        $validated['rw'] = str_pad($validated['rw'], 3, '0', STR_PAD_LEFT);

        DataKependudukan::create($validated);

        return redirect()->route('admin.kependudukan.index')
            ->with('success', "Data kependudukan warga {$validated['nama_lengkap']} (NIK: {$validated['nik']}) berhasil ditambahkan ke master data.");
    }

    /**
     * Perbarui data kependudukan yang sudah ada.
     */
    public function update(Request $request, DataKependudukan $kependudukan): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:data_kependudukan,nik,'.$kependudukan->id],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string', 'max:500'],
            'rt' => ['required', 'string', 'max:3'],
            'rw' => ['required', 'string', 'max:3'],
            'dusun' => ['required', 'string', 'max:255'],
        ], [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah digunakan oleh data penduduk lain.',
            'nama_lengkap.required' => 'Nama lengkap warga wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'alamat.required' => 'Alamat domisili wajib diisi.',
            'rt.required' => 'RT wajib diisi.',
            'rw.required' => 'RW wajib diisi.',
            'dusun.required' => 'Nama dusun wajib diisi.',
        ]);

        $validated['nama_lengkap'] = Str::of($validated['nama_lengkap'])->squish()->upper()->toString();
        $validated['rt'] = str_pad($validated['rt'], 3, '0', STR_PAD_LEFT);
        $validated['rw'] = str_pad($validated['rw'], 3, '0', STR_PAD_LEFT);

        $kependudukan->update($validated);

        return redirect()->route('admin.kependudukan.index')
            ->with('success', "Data kependudukan warga {$kependudukan->nama_lengkap} (NIK: {$kependudukan->nik}) berhasil diperbarui.");
    }

    /**
     * Hapus data kependudukan dari master data desa.
     */
    public function destroy(DataKependudukan $kependudukan): RedirectResponse
    {
        $nama = $kependudukan->nama_lengkap;
        $nik = $kependudukan->nik;

        $kependudukan->delete();

        return redirect()->route('admin.kependudukan.index')
            ->with('success', "Data kependudukan warga {$nama} (NIK: {$nik}) berhasil dihapus dari master data.");
    }
}
