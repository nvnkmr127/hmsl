<?php

namespace App\Mail;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrescriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $prescription;

    public function __construct(Prescription $prescription)
    {
        $this->prescription = $prescription;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Prescription from ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prescription',
        );
    }
}
