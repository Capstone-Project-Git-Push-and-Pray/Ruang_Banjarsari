<?php

namespace App\Services;

use App\Enums\AksiModerasi;
use App\Enums\FiturSanksi;
use App\Models\RiwayatModerasi;
use App\Models\Sanksi;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class SanksiService
{
    /**
     * Terapkan sanksi bertingkat otomatis setelah konten warga dihapus karena pelanggaran (PBI #46, PBI #47).
     * Dipanggil di dalam transaksi penghapusan konten.
     *
     * @return Sanksi|null Null jika penghapusan tidak memicu sanksi.
     */
    public function terapkanOtomatis(RiwayatModerasi $riwayat): ?Sanksi
    {
        if ($riwayat->aksi !== AksiModerasi::Hapus || $riwayat->alasan?->memicuSanksi() !== true) {
            return null;
        }

        // Kunci baris warga agar dua penghapusan bersamaan tidak menghasilkan tingkat yang sama
        $warga = User::query()->lockForUpdate()->find($riwayat->pemilik_id);

        if ($warga === null || ! $warga->isWarga()) {
            return null;
        }

        $fitur = FiturSanksi::untukKonten($riwayat->jenis_konten);
        $tingkat = $warga->sanksi()->where('fitur', $fitur)->dihitung()->count() + 1;
        $durasiHari = $fitur->durasiHari($tingkat);
        $mulai = now();

        return $warga->sanksi()->create([
            'fitur' => $fitur,
            'tingkat' => $tingkat,
            'riwayat_moderasi_id' => $riwayat->id,
            'mulai_pada' => $mulai,
            'berakhir_pada' => $durasiHari === null ? null : $mulai->copy()->addDays($durasiHari),
            'permanen' => $durasiHari === null,
        ]);
    }

    /**
     * Ubah masa berlaku sanksi (PBI #49).
     */
    public function ubahMasaBerlaku(Sanksi $sanksi, ?CarbonInterface $berakhirPada, User $admin, string $catatan): void
    {
        $sanksi->update([
            'berakhir_pada' => $berakhirPada,
            'permanen' => $berakhirPada === null,
            'catatan_admin' => $catatan,
            'diubah_oleh' => $admin->id,
        ]);
    }

    /**
     * Cabut sanksi (PBI #49).
     *
     * - Diakhiri: fitur dibuka kembali karena warga sudah membaik; tetap dihitung sebagai pelanggaran.
     * - Dibatalkan: sanksi keliru karena kesalahan moderasi; tidak dihitung untuk tingkat berikutnya.
     */
    public function cabut(Sanksi $sanksi, string $jenisPencabutan, User $admin, string $catatan): void
    {
        DB::transaction(function () use ($sanksi, $jenisPencabutan, $admin, $catatan): void {
            $terkunci = Sanksi::query()->lockForUpdate()->findOrFail($sanksi->id);

            if ($terkunci->jenis_pencabutan === Sanksi::PENCABUTAN_DIBATALKAN) {
                return;
            }

            $terkunci->update([
                'dicabut_pada' => $terkunci->dicabut_pada ?? now(),
                'jenis_pencabutan' => $jenisPencabutan,
                'catatan_admin' => $catatan,
                'diubah_oleh' => $admin->id,
            ]);
        });
    }
}
