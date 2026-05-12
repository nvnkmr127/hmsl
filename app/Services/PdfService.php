<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Generate and return a PDF download response.
     */
    public function download(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data);
        
        // Optional: Set paper size, orientation, etc.
        $pdf->setPaper('a5', 'portrait');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Generate and return a PDF stream (view in browser).
     */
    public function stream(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a5', 'portrait');
        
        return $pdf->stream($filename . '.pdf');
    }

    /**
     * Generate and return raw PDF binary content.
     */
    public function output(string $view, array $data)
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a5', 'portrait');
        
        return $pdf->output();
    }
}
