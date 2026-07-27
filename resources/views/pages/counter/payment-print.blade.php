@extends('layouts.print')

@section('title', 'Payment Receipt — ' . $payment->id)

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    /* Premium Print Styles for A5 */
    @media print {
        @page { 
            size: A5 portrait; 
            margin: 6mm 5mm; 
        }
        body { 
            font-size: 8pt; 
            background: #fff !important; 
            color: #1e293b; 
            font-family: 'Outfit', sans-serif; 
        }
        .print-container { 
            padding: 0 !important; 
            margin: 0 !important; 
            width: 100% !important; 
        }
    }

    body { 
        font-family: 'Outfit', sans-serif; 
        color: #1e293b; 
        line-height: 1.3; 
        font-size: 8.5pt; 
    }
    
    /* Elegant Header */
    .header-layout {
        display: flex;
        align-items: center;
        gap: 15px;
        padding-bottom: 12px;
        border-bottom: 3px solid #0f172a;
        margin-bottom: 15px;
    }
    .logo-box img {
        height: 60px;
        width: auto;
    }
    .hospital-info {
        flex: 1;
    }
    .hospital-info h1 {
        margin: 0;
        font-size: 16pt;
        font-weight: 900;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: -0.01em;
    }
    .hospital-info p {
        margin: 1px 0;
        font-size: 7.5pt;
        font-weight: 500;
        color: #64748b;
    }
    .bill-title-meta {
        text-align: right;
    }
    .bill-title-meta h2 {
        margin: 0;
        font-size: 14pt;
        font-weight: 900;
        color: #0f172a;
    }
    .bill-title-meta .invoice-no {
        font-weight: 800;
        font-size: 9pt;
        color: #6366f1;
    }

    /* Clean Patient Grid */
    .patient-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
        padding: 0 5px;
    }
    .info-row {
        display: flex;
        margin-bottom: 4px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 2px;
    }
    .info-label {
        width: 100px;
        font-weight: 600;
        color: #64748b;
        font-size: 7.5pt;
        text-transform: uppercase;
    }
    .info-val {
        flex: 1;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
    }

    /* Billing Table Redesign */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .items-table th {
        text-align: left;
        padding: 8px;
        background: #0f172a;
        color: #fff;
        font-size: 7.5pt;
        text-transform: uppercase;
        font-weight: 700;
    }
    .items-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }
    
    .amount-in-words {
        margin-top: 15px;
        padding: 8px;
        background: #f8fafc;
        border-radius: 4px;
        font-style: italic;
        font-weight: 600;
        color: #475569;
        font-size: 7.5pt;
    }

    /* Footer Aesthetics */
    .footer-signature {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .signature-placeholder {
        text-align: center;
        width: 160px;
    }
    .sig-line {
        border-top: 1.5px solid #0f172a;
        margin-top: 35px;
        padding-top: 4px;
        font-weight: 800;
        font-size: 8pt;
        text-transform: uppercase;
    }
    .print-meta {
        font-size: 6.5pt;
        color: #94a3b8;
    }
</style>

<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
    <img src="{{ asset('images/DW_header.png') }}" alt="Hospital Header" style="max-width: 100%; height: auto; max-height: 90px; object-fit: contain;">
</div>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 15px;">
    <h2 style="margin: 0; font-size: 14pt; font-weight: 900; text-decoration: underline; color: #000;">PAYMENT RECEIPT</h2>
    <div class="invoice-no" style="font-weight: 800; font-size: 9pt; color: #000;">#RCT-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</div>
</div>

<div class="patient-section">
    <div>
        <div class="info-row"><span class="info-label">Patient Name</span><span class="info-val">{{ $payment->bill->patient->full_name }}</span></div>
        <div class="info-row"><span class="info-label">Age / Gender</span><span class="info-val">{{ $payment->bill->patient->age }} / {{ $payment->bill->patient->gender }}</span></div>
        <div class="info-row"><span class="info-label">UHID (MRN)</span><span class="info-val">{{ $payment->bill->patient->uhid }}</span></div>
    </div>
    <div>
        <div class="info-row"><span class="info-label">Date</span><span class="info-val">
            {{ ($payment->received_at ?? $payment->created_at)->format('d M, Y h:i A') }}
        </span></div>
        @if($payment->bill->admission)
            <div class="info-row"><span class="info-label">IP Number</span><span class="info-val">{{ $payment->bill->admission->admission_number }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">Bill Number</span><span class="info-val">{{ $payment->bill->bill_number }}</span></div>
    </div>
</div>

<table class="items-table">
    <thead>
        <tr>
            <th>Description</th>
            <th style="text-align: center; width: 100px;">Payment Mode</th>
            <th style="text-align: right; width: 150px;">Amount Paid</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight: 500;">
                {{ $payment->type === 'refund' ? 'Refund Processed' : 'Payment Received' }} 
                @if($payment->reference)
                    <br><span style="font-size: 7pt; color: #64748b;">Ref: {{ $payment->reference }}</span>
                @endif
                @if($payment->notes)
                    <br><span style="font-size: 7pt; color: #64748b;">Notes: {{ $payment->notes }}</span>
                @endif
            </td>
            <td style="text-align: center;">{{ strtoupper($payment->method ?? 'Cash') }}</td>
            <td style="text-align: right; font-weight: 900; font-size: 12pt;">₹{{ number_format($payment->amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="amount-in-words">
    RUPEES {{ strtoupper(\Illuminate\Support\Number::spell((float) $payment->amount)) }} ONLY
</div>

<div class="footer-signature">
    <div class="print-meta">
        Received by: {{ strtoupper($payment->receiver?->name ?? 'Admin') }}<br>
        Date: {{ now()->format('d/m/Y H:i:s') }}
    </div>
    <div class="signature-placeholder">
        <div class="sig-line">Authorized Signatory</div>
    </div>
</div>

@endsection
