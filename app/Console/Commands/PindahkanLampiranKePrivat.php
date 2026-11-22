<?php

namespace App\Console\Commands;

use App\Models\Lampiran;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('lampiran:pindahkan-ke-privat')]
#[Description('Pindahkan foto lampiran lama dari disk public ke disk privat (PBI #38)')]
class PindahkanLampiranKePrivat extends Command
{
    /**
     * Pindahkan seluruh berkas lampiran. Berkas publik baru dihapus setelah salinannya terbukti identik,
     * sehingga perintah ini aman dijalankan berulang kali.
     */
    public function handle(): int
    {
        $asal = Storage::disk('public');
        $tujuan = Storage::disk(Lampiran::DISK);
        $dipindahkan = 0;
        $gagal = 0;

        foreach ($asal->allFiles(Lampiran::FOLDER) as $path) {
            if (! $tujuan->exists($path)) {
                $tujuan->writeStream($path, $asal->readStream($path));
            }

            if (! $tujuan->exists($path) || $tujuan->checksum($path) !== $asal->checksum($path)) {
                $this->error("Gagal memindahkan {$path}; berkas publik tidak dihapus.");
                $gagal++;

                continue;
            }

            if (! $asal->delete($path)) {
                $this->error("Salinan privat dibuat, tetapi {$path} gagal dihapus dari disk public.");
                $gagal++;

                continue;
            }

            $dipindahkan++;
        }

        $this->info("{$dipindahkan} berkas dipindahkan, {$gagal} gagal.");

        return $gagal === 0 ? self::SUCCESS : self::FAILURE;
    }
}
