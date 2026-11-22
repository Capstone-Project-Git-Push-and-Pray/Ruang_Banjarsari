<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\StatusAkun;
use App\Models\DataKependudukan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrasiWargaService
{
    /**
     * Normalisasi nama lengkap untuk pencocokan:
     * - Menghapus spasi di awal dan akhir (trim)
     * - Mengubah spasi ganda / berulang menjadi spasi tunggal
     * - Mengubah seluruh huruf menjadi huruf kapital (case-insensitive)
     */
    public function normalisasiNama(?string $nama): string
    {
        if ($nama === null) {
            return '';
        }

        return (string) Str::of($nama)->squish()->upper();
    }

    /**
     * Normalisasi teks umum (alamat, dusun, dsb).
     */
    public function normalisasiTeks(?string $teks): string
    {
        if ($teks === null) {
            return '';
        }

        return (string) Str::of($teks)->squish()->upper();
    }

    /**
     * Cocokkan data pendaftar dengan master data kependudukan desa (mastersheet).
     *
     * Kriteria:
     * - Jika SEMUA data pendaftar (nama, tanggal lahir, alamat, RT, RW, dusun) SESUAI dengan master data,
     *   maka akun langsung aktif (StatusAkun::Aktif) tanpa perlu persetujuan oleh admin desa.
     * - Jika terdapat data yang TIDAK SAMA / BERBEDA dengan yang ada pada mastersheet,
     *   atau NIK tidak ditemukan dalam master data, maka wajib melalui proses peninjauan
     *   atau persetujuan oleh Admin Desa (StatusAkun::MenungguTinjauan).
     *
     * @param  array<string, mixed>|string  $dataOrNik
     */
    public function cocokkanDenganDataKependudukan(array|string $dataOrNik, ?string $namaLengkap = null): bool
    {
        if (is_string($dataOrNik)) {
            $kependudukan = DataKependudukan::where('nik', $dataOrNik)->first();

            if (! $kependudukan) {
                return false;
            }

            return $this->normalisasiNama($namaLengkap) === $this->normalisasiNama($kependudukan->nama_lengkap);
        }

        $data = $dataOrNik;
        $nik = $data['nik'] ?? '';
        $kependudukan = DataKependudukan::where('nik', $nik)->first();

        if (! $kependudukan) {
            return false;
        }

        // 1. Nama Lengkap
        if ($this->normalisasiNama($data['nama_lengkap'] ?? '') !== $this->normalisasiNama($kependudukan->nama_lengkap)) {
            return false;
        }

        // 2. Tanggal Lahir (jika pendaftar dan master data memiliki tanggal lahir)
        if (! empty($data['tanggal_lahir']) && $kependudukan->tanggal_lahir !== null) {
            $pendaftarTgl = $data['tanggal_lahir'] instanceof \DateTimeInterface
                ? $data['tanggal_lahir']->format('Y-m-d')
                : Carbon::parse($data['tanggal_lahir'])->format('Y-m-d');
            $masterTgl = $kependudukan->tanggal_lahir->format('Y-m-d');

            if ($pendaftarTgl !== $masterTgl) {
                return false;
            }
        }

        // 3. Alamat KTP (jika pendaftar dan master data memiliki alamat)
        if (isset($data['alamat']) && ! empty($kependudukan->alamat)) {
            if ($this->normalisasiTeks($data['alamat']) !== $this->normalisasiTeks($kependudukan->alamat)) {
                return false;
            }
        }

        // 4. RT (jika pendaftar dan master data memiliki RT)
        if (isset($data['rt']) && isset($kependudukan->rt) && $data['rt'] !== '' && $kependudukan->rt !== '') {
            if (ltrim((string) $data['rt'], '0') !== ltrim((string) $kependudukan->rt, '0')) {
                return false;
            }
        }

        // 5. RW (jika pendaftar dan master data memiliki RW)
        if (isset($data['rw']) && isset($kependudukan->rw) && $data['rw'] !== '' && $kependudukan->rw !== '') {
            if (ltrim((string) $data['rw'], '0') !== ltrim((string) $kependudukan->rw, '0')) {
                return false;
            }
        }

        // 6. Dusun (jika pendaftar dan master data memiliki dusun)
        if (isset($data['dusun']) && ! empty($kependudukan->dusun)) {
            if ($this->normalisasiTeks($data['dusun']) !== $this->normalisasiTeks($kependudukan->dusun)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Bandingkan rincian isian data pendaftar warga dengan data kependudukan desa.
     * Mengembalikan array field beserta status kesesuaian data.
     *
     * @return array<string, array{label: string, pendaftar: ?string, kependudukan: ?string, sama: bool}>
     */
    public function bandingkanData(User $user, ?DataKependudukan $kependudukan): array
    {
        if (! $kependudukan) {
            return [
                'nama_lengkap' => [
                    'label' => 'Nama Lengkap',
                    'pendaftar' => $user->nama_lengkap,
                    'kependudukan' => null,
                    'sama' => false,
                ],
                'tanggal_lahir' => [
                    'label' => 'Tanggal Lahir',
                    'pendaftar' => $user->tanggal_lahir?->format('Y-m-d'),
                    'kependudukan' => null,
                    'sama' => false,
                ],
                'alamat' => [
                    'label' => 'Alamat KTP',
                    'pendaftar' => $user->alamat,
                    'kependudukan' => null,
                    'sama' => false,
                ],
                'rt' => [
                    'label' => 'RT',
                    'pendaftar' => $user->rt,
                    'kependudukan' => null,
                    'sama' => false,
                ],
                'rw' => [
                    'label' => 'RW',
                    'pendaftar' => $user->rw,
                    'kependudukan' => null,
                    'sama' => false,
                ],
                'dusun' => [
                    'label' => 'Dusun',
                    'pendaftar' => $user->dusun,
                    'kependudukan' => null,
                    'sama' => false,
                ],
            ];
        }

        $pendaftarTgl = $user->tanggal_lahir?->format('Y-m-d');
        $masterTgl = $kependudukan->tanggal_lahir?->format('Y-m-d');

        return [
            'nama_lengkap' => [
                'label' => 'Nama Lengkap',
                'pendaftar' => $user->nama_lengkap,
                'kependudukan' => $kependudukan->nama_lengkap,
                'sama' => $this->normalisasiNama($user->nama_lengkap) === $this->normalisasiNama($kependudukan->nama_lengkap),
            ],
            'tanggal_lahir' => [
                'label' => 'Tanggal Lahir',
                'pendaftar' => $pendaftarTgl,
                'kependudukan' => $masterTgl,
                'sama' => $pendaftarTgl !== null && $pendaftarTgl === $masterTgl,
            ],
            'alamat' => [
                'label' => 'Alamat KTP',
                'pendaftar' => $user->alamat,
                'kependudukan' => $kependudukan->alamat,
                'sama' => $this->normalisasiTeks($user->alamat) === $this->normalisasiTeks($kependudukan->alamat),
            ],
            'rt' => [
                'label' => 'RT',
                'pendaftar' => $user->rt,
                'kependudukan' => $kependudukan->rt,
                'sama' => ltrim((string) $user->rt, '0') === ltrim((string) $kependudukan->rt, '0'),
            ],
            'rw' => [
                'label' => 'RW',
                'pendaftar' => $user->rw,
                'kependudukan' => $kependudukan->rw,
                'sama' => ltrim((string) $user->rw, '0') === ltrim((string) $kependudukan->rw, '0'),
            ],
            'dusun' => [
                'label' => 'Dusun',
                'pendaftar' => $user->dusun,
                'kependudukan' => $kependudukan->dusun,
                'sama' => $this->normalisasiTeks($user->dusun) === $this->normalisasiTeks($kependudukan->dusun),
            ],
        ];
    }

    /**
     * Daftarkan akun warga baru dengan validasi otomatis data kependudukan.
     *
     * @param  array<string, mixed>  $data
     */
    public function daftarkan(array $data): User
    {
        $isCocok = $this->cocokkanDenganDataKependudukan($data);
        $statusAkun = $isCocok ? StatusAkun::Aktif : StatusAkun::MenungguTinjauan;

        return User::create([
            'nik' => $data['nik'],
            'nama_lengkap' => $data['nama_lengkap'],
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'dusun' => $data['dusun'] ?? null,
            'email' => $data['email'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => Role::Warga,
            'status_akun' => $statusAkun,
            'persetujuan_pada' => $isCocok ? now() : null,
            'wajib_ganti_password' => false,
        ]);
    }

    /**
     * Perbaiki data warga yang ditolak dan ajukan ulang verifikasi akun.
     *
     * @param  array<string, mixed>  $data
     */
    public function ajukanUlang(User $user, array $data): User
    {
        $dataLengkap = array_merge([
            'nik' => $user->nik,
            'nama_lengkap' => $data['nama_lengkap'] ?? $user->nama_lengkap,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? $user->tanggal_lahir,
            'alamat' => $data['alamat'] ?? $user->alamat,
            'rt' => $data['rt'] ?? $user->rt,
            'rw' => $data['rw'] ?? $user->rw,
            'dusun' => $data['dusun'] ?? $user->dusun,
        ], $data);

        $isCocok = $this->cocokkanDenganDataKependudukan($dataLengkap);
        $statusAkun = $isCocok ? StatusAkun::Aktif : StatusAkun::MenungguTinjauan;

        $user->update([
            'nama_lengkap' => $data['nama_lengkap'] ?? $user->nama_lengkap,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? $user->tanggal_lahir,
            'alamat' => $data['alamat'] ?? $user->alamat,
            'rt' => $data['rt'] ?? $user->rt,
            'rw' => $data['rw'] ?? $user->rw,
            'dusun' => $data['dusun'] ?? $user->dusun,
            'email' => $data['email'] ?? $user->email,
            'telepon' => $data['telepon'] ?? $user->telepon,
            'whatsapp' => $data['whatsapp'] ?? $user->whatsapp,
            'status_akun' => $statusAkun,
            'alasan_penolakan' => null,
            'ditinjau_oleh' => null,
            'ditinjau_pada' => $isCocok ? now() : null,
            'persetujuan_pada' => $isCocok ? now() : null,
        ]);

        return $user;
    }

    /**
     * Setujui pendaftaran warga oleh Admin Desa (PBI #18).
     */
    public function setujuiPendaftaran(User $user, User $admin): User
    {
        $user->update([
            'status_akun' => StatusAkun::Aktif,
            'alasan_penolakan' => null,
            'ditinjau_oleh' => $admin->id,
            'ditinjau_pada' => now(),
        ]);

        return $user;
    }

    /**
     * Tolak pendaftaran warga oleh Admin Desa beserta alasannya (PBI #18).
     */
    public function tolakPendaftaran(User $user, User $admin, string $alasan): User
    {
        $user->update([
            'status_akun' => StatusAkun::Ditolak,
            'alasan_penolakan' => $alasan,
            'ditinjau_oleh' => $admin->id,
            'ditinjau_pada' => now(),
        ]);

        return $user;
    }
}
