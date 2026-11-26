<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    // 1. Definisikan variabel public agar bisa dibaca oleh Blade
    public $withdrawal;

    public function __construct($withdrawal)
    {
        // 2. Terima data dari Controller
        $this->withdrawal = $withdrawal;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // 3. Judul Email yang akan muncul di Inbox Clipper
            subject: 'Invoice Penarikan Dana - ' . $this->withdrawal->transaction->reference_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            // Tambahkan ini untuk memastikan variabel terkirim dengan nama 'withdrawal'
            with: [
                'withdrawal' => $this->withdrawal,
            ],
        );
    }
    
    public function attachments(): array
    {
        return [];
    }
}