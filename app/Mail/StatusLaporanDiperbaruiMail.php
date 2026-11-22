<?php

namespace App\Mail;

use App\Enums\StatusLaporan;
use App\Models\Pengaduan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusLaporanDiperbaruiMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Buat instance pesan email baru (PBI #32).
     */
    public function __construct(
        public Pengaduan $pengaduan,
        public StatusLaporan $statusLaporan,
        public ?string $catatan = null,
    ) {}

    /**
     * Dapatkan envelope pesan (subjek email).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Ruang Banjarsari] Status Laporan #{$this->pengaduan->id} Diperbarui: {$this->statusLaporan->label()}",
        );
    }

    /**
     * Dapatkan konten pesan email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.status-laporan-diperbarui',
        );
    }

    /**
     * Lampiran untuk pesan.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
