<?php

namespace App\Mail;

use App\Enums\StatusDonasi;
use App\Models\Donasi;
use App\Services\KuitansiDonasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email ke donatur saat donasi tercatat, diterima (dengan kuitansi PDF), atau ditolak.
 */
class PemberitahuanDonasi extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Donasi $donasi) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->donasi->status) {
                StatusDonasi::Menunggu => "Donasi Anda tercatat ({$this->donasi->kunci})",
                StatusDonasi::Diterima => "Terima kasih, donasi Anda sudah kami terima ({$this->donasi->kunci})",
                StatusDonasi::Ditolak => "Donasi Anda belum dapat kami konfirmasi ({$this->donasi->kunci})",
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'site.donasi.email',
            with: [
                'tautanValidasi' => route('donasi.validasi', ['kunci' => $this->donasi->kunci]),
            ],
        );
    }

    /**
     * Donasi yang diterima disertai kuitansi PDF.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->donasi->status !== StatusDonasi::Diterima) {
            return [];
        }

        return [
            Attachment::fromData(fn (): string => KuitansiDonasi::pdf($this->donasi)->output(), KuitansiDonasi::namaFile($this->donasi))
                ->withMime('application/pdf'),
        ];
    }
}
