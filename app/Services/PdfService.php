<?php

namespace App\Services;

use App\Models\Admission;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generateDischargeSummaryPdf(Admission $admission)
    {
        $admission->load([
            'patient',
            'bed.ward',
            'doctor.user',
            'dischargeSummary.medications',
            'ipdVitals' => fn($q) => $q->latest()->limit(5),
            'ipdMedications' => fn($q) => $q->where('status', 'Active'),
            'labOrders.labTest',
            'finalBill.items',
            'diagnoses',
        ]);

        $pdf = Pdf::loadView('pages.discharge.summary-print', [
            'admission' => $admission,
            'summary' => $admission->dischargeSummary,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function downloadDischargeSummary(Admission $admission)
    {
        $pdf = $this->generateDischargeSummaryPdf($admission);

        $filename = 'discharge_summary_' . $admission->admission_number . '.pdf';

        return $pdf->download($filename);
    }

    public function streamDischargeSummary(Admission $admission)
    {
        $pdf = $this->generateDischargeSummaryPdf($admission);

        return $pdf->stream();
    }
}
