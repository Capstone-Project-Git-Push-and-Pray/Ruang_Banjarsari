<?php

namespace App\Notifications;

use App\Models\PengingatTindakLanjut;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi lonceng untuk Admin Desa saat Kepala Desa mengirim pengingat tindak lanjut (PBI #53).
 */
class PengingatTindakLanjutNotification extends Notification
{
    public function __construct(public PengingatTindakLanjut $pengingat) {}

    /**
     * Kanal pengiriman notifikasi.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan pada tabel notifications.
     *
     * @return array{pengingat_id: int, pengaduan_id: int, jenis: string, judul: string, catatan: ?string, pengirim: string}
     */
    public function toArray(object $notifiable): array
    {
        $laporan = $this->pengingat->pengaduan;

        return [
            'pengingat_id' => $this->pengingat->id,
            'pengaduan_id' => $laporan->id,
            'jenis' => $laporan->jenis->value,
            'judul' => $laporan->judul,
            'catatan' => $this->pengingat->catatan,
            'pengirim' => $this->pengingat->pengirim->nama_lengkap,
        ];
    }
}
