<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Prescription;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrescriptionMail;
use App\Mail\InvoiceMail;

class CommunicationService
{
    public function sendPrescription(Prescription $prescription)
    {
        $patient = $prescription->patient;
        if (!$patient->email) {
            throw new \Exception("Patient does not have an email address.");
        }

        Mail::to($patient->email)->send(new PrescriptionMail($prescription));
    }

    public function sendInvoice(Bill $bill)
    {
        $patient = $bill->patient;
        if (!$patient->email) {
            throw new \Exception("Patient does not have an email address.");
        }

        Mail::to($patient->email)->send(new InvoiceMail($bill));
    }
}
