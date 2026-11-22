<?php

namespace App\Services;

use App\Enums\AksiModerasi;
use App\Enums\AlasanModerasi;
use App\Enums\JenisKonten;
use App\Models\Komentar;
use App\Models\Pengaduan;
use App\Models\RiwayatModerasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ModerasiService
{
    /**
     * Sembunyikan konten dari publik dan catat keputusannya (PBI #44).
     *
     * @return bool False jika konten sudah disembunyikan atau sudah dihapus sebelumnya.
     */
    public function sembunyikan(Pengaduan|Komentar $konten, User $admin): bool
    {
        return DB::transaction(function () use ($konten, $admin): bool {
            $terkunci = $konten->newQuery()->lockForUpdate()->find($konten->getKey());

            if ($terkunci === null || $terkunci->isDisembunyikan()) {
                return false;
            }

            $this->simpanVisibilitas($terkunci, now(), $admin->id);
            $this->catat($terkunci, AksiModerasi::Sembunyikan, $admin);

            return true;
        });
    }

    /**
     * Tampilkan kembali konten yang disembunyikan dan catat keputusannya (PBI #44).
     *
     * @return bool False jika konten memang sedang tampil atau sudah dihapus.
     */
    public function tampilkan(Pengaduan|Komentar $konten, User $admin): bool
    {
        return DB::transaction(function () use ($konten, $admin): bool {
            $terkunci = $konten->newQuery()->lockForUpdate()->find($konten->getKey());

            if ($terkunci === null || ! $terkunci->isDisembunyikan()) {
                return false;
            }

            $this->simpanVisibilitas($terkunci, null, null);
            $this->catat($terkunci, AksiModerasi::Tampilkan, $admin);

            return true;
        });
    }

    /**
     * Hapus konten (soft delete) beserta alasan yang tercatat (PBI #45).
     *
     * @return bool False jika konten sudah dihapus sebelumnya.
     */
    public function hapus(Pengaduan|Komentar $konten, User $admin, AlasanModerasi $alasan, ?string $catatan = null): bool
    {
        return DB::transaction(function () use ($konten, $admin, $alasan, $catatan): bool {
            $terkunci = $konten->newQuery()->lockForUpdate()->find($konten->getKey());

            if ($terkunci === null) {
                return false;
            }

            $this->catat($terkunci, AksiModerasi::Hapus, $admin, $alasan, $catatan);
            $terkunci->delete();

            return true;
        });
    }

    /**
     * Ubah penanda visibilitas tanpa mengubah waktu pembaruan isi konten.
     */
    private function simpanVisibilitas(Pengaduan|Komentar $konten, mixed $pada, ?int $adminId): void
    {
        $konten->timestamps = false;
        $konten->forceFill([
            'disembunyikan_pada' => $pada,
            'disembunyikan_oleh' => $adminId,
        ])->save();
    }

    /**
     * Simpan catatan keputusan moderasi beserta salinan isi konten.
     */
    private function catat(
        Pengaduan|Komentar $konten,
        AksiModerasi $aksi,
        User $admin,
        ?AlasanModerasi $alasan = null,
        ?string $catatan = null,
    ): void {
        $isAspirasi = $konten instanceof Pengaduan;

        RiwayatModerasi::create([
            'jenis_konten' => $isAspirasi ? JenisKonten::Aspirasi : JenisKonten::Komentar,
            'pengaduan_id' => $isAspirasi ? $konten->id : $konten->pengaduan_id,
            'komentar_id' => $isAspirasi ? null : $konten->id,
            'pemilik_id' => $konten->user_id,
            'aksi' => $aksi,
            'alasan' => $alasan,
            'catatan' => $catatan,
            'isi_konten' => $isAspirasi ? $konten->judul."\n\n".$konten->deskripsi : $konten->isi,
            'dilakukan_oleh' => $admin->id,
        ]);
    }
}
