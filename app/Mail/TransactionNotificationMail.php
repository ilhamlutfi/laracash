<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction) {}

    public function envelope(): Envelope
    {
        // Mapping tipe transaksi ke Bahasa Indonesia
        $typesInIndonesia = [
            'income'   => 'Pemasukan',
            'expense'  => 'Pengeluaran',
            'transfer' => 'Transfer',
        ];

        $typeLabel = $typesInIndonesia[$this->transaction->type] ?? ucfirst($this->transaction->type);
        $formattedAmount = number_format($this->transaction->amount, 0, ',', '.');

        return new Envelope(
            from: 'mubatekno@gmail.com',
            subject: "[CASHAPP] Transaksi {$typeLabel} - Rp {$formattedAmount}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction-notification',
        );
    }
}
