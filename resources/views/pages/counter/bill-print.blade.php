@extends('layouts.print')

@section('title', 'Final Bill — ' . $bill->bill_number)

@section('content')

{{-- 1. Space for Pre-printed Letterhead (7cm as per system style in OPD slip) --}}
<div style="height: 7cm; width: 100%;"></div>

<link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    /* System Style Print Aesthetics */
    @media print {
        body { font-size: 8pt; background: #fff !important; color: #000; font-family: 'Inter', sans-serif; }
        .print-container { padding: 5mm !important; margin: 0 !important; width: 100% !important; }
        .no-print { display: none !important; }
        .table th { background: #f8fafc !important; -webkit-print-color-adjust: exact; border: 1.5px solid #000 !important; }
        .table td { border: 1px solid #e2e8f0 !important; }
        tr { page-break-inside: avoid; }
    }

    body { font-family: 'Inter', sans-serif; color: #0f172a; line-height: 1.1; font-size: 8.5pt; }
    
    .bill-title-container { border-bottom: 2px solid #000; margin-bottom: 8px; padding-bottom: 4px; display: flex; justify-content: space-between; align-items: flex-end; }
    .bill-title-container h2 { font-size: 11pt; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }
    .gst-info { font-size: 7.5pt; font-weight: 700; color: #475569; }

    /* Patient Info Grid */
    .patient-details-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 12px; border: 1.5px solid #000; padding: 10px; margin-bottom: 10px; background: #fff; }
    .info-line { display: flex; font-size: 9pt; margin-bottom: 1px; }
    .info-label { width: 115px; font-weight: 600; color: #475569; }
    .info-colon { width: 15px; text-align: center; }
    .info-value { font-weight: 800; color: #000; flex: 1; text-transform: uppercase; }

    /* Table Styles (Dwarakamai Style) */
    .billing-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .billing-table th { background: #f1f5f9; border: 1.5px solid #000; padding: 6px 4px; font-size: 8.5pt; text-align: left; text-transform: uppercase; font-weight: 900; color: #000; }
    .billing-table td { padding: 4px; font-size: 9pt; border: 1px solid #e2e8f0; vertical-align: top; }
    
    .group-header-row td { background: #f8fafc; font-weight: 900; font-size: 9pt; color: #4f46e5; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 6px 6px; }
    .group-total-row td { background: #fff; border-top: 1px solid #000; font-weight: 900; text-align: right; padding: 5px; font-size: 9pt; text-transform: uppercase; }

    /* Amount in Words Area */
    .amount-words-section { margin: 10px 0; font-size: 9pt; font-weight: 700; padding: 5px; border: 1px dashed #cbd5e1; }

    /* Summary & Receipts (Split Layout) */
    .summary-grid { display: grid; grid-template-columns: 1fr 280px; gap: 15px; }
    
    .receipts-box { border: 1.5px solid #000; height: fit-content; }
    .receipts-table { width: 100%; border-collapse: collapse; }
    .receipts-table th { background: #f8fafc; border-bottom: 1.5px solid #000; font-size: 7.5pt; font-weight: 800; text-align: left; padding: 4px; text-transform: uppercase; }
    .receipts-table td { padding: 3px 5px; border-bottom: 1px solid #f1f5f9; font-size: 8pt; vertical-align: middle; }
    
    .totals-column { display: flex; flex-direction: column; gap: 0; border: 1.5px solid #000; background: #fff; }
    .total-row { display: flex; padding: 4px 10px; font-size: 9.5pt; border-bottom: 1px solid #f1f5f9; }
    .total-label { flex: 1; font-weight: 600; color: #475569; }
    .total-colon { width: 15px; text-align: center; }
    .total-value { width: 110px; text-align: right; font-weight: 800; color: #000; }
    .total-row.grand { background: #f8fafc; border-top: 2px solid #000; border-bottom: none; font-weight: 900; padding: 8px 10px; }

    /* Barcodes and Signatures */
    .bottom-identity { margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-end; }
    .barcode-group { display: flex; gap: 40px; }
    .barcode-item { text-align: center; }
    .barcode-display { font-family: 'Libre Barcode 128', cursive; font-size: 32pt; line-height: 1; margin: 0; color: #000; }
    .barcode-label { font-size: 7pt; font-weight: 700; margin-top: -2px; text-transform: uppercase; color: #475569; }

    .signature-area { text-align: center; min-width: 180px; }
    .sig-line { border-top: 2px solid #000; padding-top: 4px; font-weight: 900; font-size: 9pt; text-transform: uppercase; margin-top: 40px; }
    .staff-name { font-size: 7.5pt; font-weight: 700; color: #475569; margin-bottom: 2px; }

    /* Print Specific Spacing */
    @page { margin: 15mm; }
</style>

<div class="bill-title-container">
    <h2>FINAL BILL - IP/OP DETAILS</h2>
    <div class="gst-info">GSTIN: {{ \App\Models\Setting::get('hospital_gst', '36AAPPD1234F1Z1') }}</div>
</div>

<div class="patient-details-grid">
    <div class="info-group">
        <div class="info-line"><span class="info-label">Patient Name</span><span class="info-colon">:</span><span class="info-value">{{ strtoupper($bill->patient->full_name) }}</span></div>
        <div class="info-line"><span class="info-label">Gender / Age</span><span class="info-colon">:</span><span class="info-value">{{ $bill->patient->gender }} / {{ $bill->patient->age }}</span></div>
        <div class="info-line"><span class="info-label">Guardian Info</span><span class="info-colon">:</span><span class="info-value">
            @if($bill->patient->mother_name) MOTHER: {{ strtoupper($bill->patient->mother_name) }} @elseif($bill->patient->father_name) FATHER: {{ strtoupper($bill->patient->father_name) }} @else SELF @endif
        </span></div>
        <div class="info-line"><span class="info-label">Contact No</span><span class="info-colon">:</span><span class="info-value">{{ $bill->patient->phone ?? '—' }}</span></div>
        <div class="info-line"><span class="info-label">Admitting Unit</span><span class="info-colon">:</span><span class="info-value">{{ strtoupper($bill->admission?->doctor?->full_name ?? $bill->consultation?->doctor?->full_name ?? 'MEDICAL TEAM') }}</span></div>
        <div class="info-line"><span class="info-label">Patient Address</span><span class="info-colon">:</span><span class="info-value">{{ strtoupper($bill->patient->address) }}, {{ strtoupper($bill->patient->city) }}</span></div>
    </div>
    <div class="info-group">
        <div class="info-line"><span class="info-label">UHID (MR No)</span><span class="info-colon">:</span><span class="info-value">{{ $bill->patient->uhid ?? '—' }}</span></div>
        <div class="info-line"><span class="info-label">IP Number</span><span class="info-colon">:</span><span class="info-value">{{ $bill->admission?->admission_number ?? '—' }}</span></div>
        <div class="info-line"><span class="info-label">Invoice Number</span><span class="info-colon">:</span><span class="info-value">{{ $bill->bill_number }}</span></div>
        <div class="info-line"><span class="info-label">Invoice Date</span><span class="info-colon">:</span><span class="info-value">{{ $bill->created_at->format('d/m/Y h:i A') }}</span></div>
        <div class="info-line"><span class="info-label">IP Admn. Date</span><span class="info-colon">:</span><span class="info-value">{{ $bill->admission?->admission_date?->format('d/m/Y h:i A') ?? '—' }}</span></div>
        <div class="info-line"><span class="info-label">Ward / Bed</span><span class="info-colon">:</span><span class="info-value">{{ strtoupper($bill->admission?->ward_name ?? 'OPD SERVICE') }}</span></div>
    </div>
</div>

<table class="billing-table">
    <thead>
        <tr>
            <th style="width: 85px;">Date</th>
            <th>Description of Services</th>
            <th style="width: 80px; text-align: right;">Unit Rate</th>
            <th style="width: 40px; text-align: center;">Qty</th>
            <th style="width: 80px; text-align: right;">Total (₹)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $groupNames = [
                'Room' => 'ACCOMODATION',
                'Procedure' => 'SURGERY / PROCEDURES',
                'Package' => 'PACKAGE',
                'Surgery' => 'SURGERY CHARGES',
                'Consultation' => 'CONSULTATION',
                'Lab' => 'INVESTIGATIONS',
                'Service' => 'SERVICES',
                'Medicine' => 'PHARMACY AND CONSUMABLES',
                'Return' => 'PHARMACY RETURNS',
            ];
            
            $groupedItems = $bill->items->groupBy(function($item) {
                return $item->item_type ?? 'Service';
            });
        @endphp

        @foreach($groupedItems as $type => $items)
            @php 
                $label = $groupNames[$type] ?? strtoupper($type);
            @endphp
            <tr class="group-header-row">
                <td colspan="5">{{ $label }}</td>
            </tr>
            @foreach($items as $item)
                <tr>
                    <td style="font-size: 8pt; color: #000; border: 1px solid #e2e8f0;">{{ $item->created_at->format('d/m/Y') }}</td>
                    <td style="font-weight: 500; border: 1px solid #e2e8f0;">{{ strtoupper($item->item_name) }}</td>
                    <td style="text-align: right; border: 1px solid #e2e8f0;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: center; border: 1px solid #e2e8f0;">{{ $item->quantity }}.0</td>
                    <td style="text-align: right; font-weight: 800; border: 1px solid #e2e8f0;">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
            <tr class="group-total-row">
                <td colspan="4" style="border: none; padding-right: 15px;">{{ $label }} TOTAL:</td>
                <td style="border: 1px solid #000; background: #fff;">{{ number_format($items->sum('total_price'), 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="amount-words-section">
    IN WORDS: RUPEES {{ strtoupper($bill->amount_in_words) }} ONLY
</div>

<div class="summary-grid">
    <div class="receipts-box">
        <table class="receipts-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Record Date</th>
                    <th>Ref No.</th>
                    <th>Payment Source</th>
                    <th style="text-align: right;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->payments as $index => $payment)
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                        <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                        <td style="font-family: monospace;">{{ $payment->transaction_id ?? $payment->id + 1000 }}</td>
                        <td style="text-transform: uppercase; font-weight: 600;">{{ $payment->method }}</td>
                        <td style="text-align: right; font-weight: 800;">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 900; border-top: 1.5px solid #000;">
                    <td colspan="4" style="text-align: right; padding: 6px;">Total Collections:</td>
                    <td style="text-align: right; padding: 6px;">{{ number_format($bill->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="totals-column">
        <div class="total-row">
            <span class="total-label">Gross Amount</span><span class="total-colon">:</span>
            <span class="total-value">{{ number_format($bill->subtotal, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total Tax Amount</span><span class="total-colon">:</span>
            <span class="total-value">{{ number_format($bill->tax_amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Discounts</span><span class="total-colon">:</span>
            <span class="total-value">{{ number_format($bill->discount_amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total Net Bill</span><span class="total-colon">:</span>
            <span class="total-value">{{ number_format($bill->total_amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Amount Paid</span><span class="total-colon">:</span>
            <span class="total-value">{{ number_format($bill->paid_amount, 2) }}</span>
        </div>
        <div class="total-row grand">
            <span class="total-label" style="font-size: 10pt;">Balance Payable</span><span class="total-colon">:</span>
            <span class="total-value" style="font-size: 11pt;">{{ number_format($bill->balance_amount, 2) }}</span>
        </div>
    </div>
</div>

<div class="bottom-identity">
    <div class="barcode-group">
        <div class="barcode-item">
            <p class="barcode-display">*{{ $bill->patient->uhid ?? '2024001' }}*</p>
            <p class="barcode-label">UHID (MR NO)</p>
        </div>
        <div class="barcode-item">
            <p class="barcode-display">*{{ $bill->bill_number }}*</p>
            <p class="barcode-label">Invoice Number</p>
        </div>
    </div>
    
    <div class="signature-area">
        <div class="staff-name">Auth. User: {{ strtoupper($bill->creator?->name ?? 'Admin') }}</div>
        <div class="sig-line">Authorized Signatory</div>
    </div>
</div>

@endsection
