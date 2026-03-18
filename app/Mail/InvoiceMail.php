<?php

namespace App\Mail;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bill;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice for Your Visit - ' . $this->bill->bill_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }
}
