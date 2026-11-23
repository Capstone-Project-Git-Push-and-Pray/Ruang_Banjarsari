<?php

namespace App\Http\Requests;

use App\Enums\FiturSanksi;
use App\Enums\JenisLaporan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengaduanRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     * Kriteria PBI #24: Hanya akun Warga berstatus Aktif yang dapat mengajukan laporan.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->isWarga() && $user->isAktif();
    }

    /**
     * Aturan validasi untuk pengajuan pengaduan dan aspirasi warga (PBI #24 & PBI #25).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::enum(JenisLaporan::class), $this->tolakAspirasiSaatSanksi(...)],
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'judul' => ['required', 'string', 'min:5', 'max:255'],
            'deskripsi' => ['required', 'string', 'min:10'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            // PBI #25: Maksimal 3 foto pendukung berformat JPG/PNG dengan ukuran maks 2 MB (2048 KB)
            'foto' => ['nullable', 'array', 'max:3'],
            'foto.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Tolak aspirasi baru selama fitur aspirasi warga dibatasi sanksi (PBI #46, PBI #48).
     */
    private function tolakAspirasiSaatSanksi(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== JenisLaporan::Aspirasi->value) {
            return;
        }

        $sanksi = $this->user()->sanksiBerlaku(FiturSanksi::Aspirasi);

        if ($sanksi !== null) {
            $fail('Fitur aspirasi Anda sedang dibatasi. '.$sanksi->keteranganMasaBerlaku().'. Pengaduan tetap dapat diajukan.');
        }
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis laporan (Pengaduan atau Aspirasi) wajib dipilih.',
            'kategori_id.required' => 'Kategori laporan wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid atau belum terdaftar.',
            'judul.required' => 'Judul laporan wajib diisi.',
            'judul.min' => 'Judul laporan minimal terdiri dari 5 karakter.',
            'judul.max' => 'Judul laporan maksimal 255 karakter.',
            'deskripsi.required' => 'Deskripsi laporan wajib diisi.',
            'deskripsi.min' => 'Deskripsi laporan minimal terdiri dari 10 karakter.',
            'lokasi.max' => 'Lokasi kejadian maksimal 255 karakter.',
            'foto.array' => 'Format unggahan foto tidak valid.',
            'foto.max' => 'Anda maksimal hanya dapat melampirkan 3 foto.',
            'foto.*.image' => 'Setiap berkas lampiran harus berupa file gambar.',
            'foto.*.mimes' => 'Format foto yang diperbolehkan hanya JPG dan PNG.',
            'foto.*.max' => 'Ukuran masing-masing file foto tidak boleh melebihi 2 MB.',
        ];
    }
}
