<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Pengaduan;
use App\Models\PengingatTindakLanjut;
use App\Models\User;
use App\Notifications\PengingatTindakLanjutNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Pengiriman pengingat tindak lanjut dari Kepala Desa ke seluruh Admin Desa (PBI #50, #51).
 */
class PengingatTindakLanjutService
{
    /**
     * Kirim pengingat untuk laporan yang perlu ditindaklanjuti, lalu beri tahu semua Admin Desa.
     *
     * @throws ValidationException bila laporan tidak memenuhi syarat atau masih dalam jeda 24 jam
     */
    public function kirim(Pengaduan $pengaduan, User $kepalaDesa, ?string $catatan): PengingatTindakLanjut
    {
        $pengingat = DB::transaction(function () use ($pengaduan, $kepalaDesa, $catatan): PengingatTindakLanjut {
            // Kunci baris laporan agar dua klik bersamaan tidak menghasilkan dua pengingat
            $laporan = Pengaduan::query()->lockForUpdate()->findOrFail($pengaduan->getKey());

            if (! $laporan->perluTindakLanjut()) {
                throw ValidationException::withMessages([
                    'pengingat' => 'Pengingat hanya dapat dikirim untuk laporan berstatus Diajukan yang lebih dari '
                        .Pengaduan::BATAS_TERTUNDA_HARI.' hari belum ditanggapi.',
                ]);
            }

            $terakhir = $laporan->pengingat()->latest()->latest('id')->first();

            if ($terakhir?->masihDalamJeda()) {
                $bolehLagi = $terakhir->created_at->copy()->addHours(PengingatTindakLanjut::JEDA_JAM);

                throw ValidationException::withMessages([
                    'pengingat' => 'Laporan ini sudah diingatkan '.$terakhir->created_at->locale('id')->diffForHumans()
                        .'. Pengingat berikutnya dapat dikirim '.$bolehLagi->locale('id')->diffForHumans().'.',
                ]);
            }

            return $laporan->pengingat()->create([
                'dikirim_oleh' => $kepalaDesa->id,
                'catatan' => filled($catatan) ? trim($catatan) : null,
            ]);
        });

        Notification::send($this->adminDesa(), new PengingatTindakLanjutNotification($pengingat->load(['pengaduan', 'pengirim'])));

        return $pengingat;
    }

    /**
     * Seluruh akun Admin Desa penerima pengingat.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function adminDesa(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->where('role', Role::AdminDesa)->get();
    }
}
