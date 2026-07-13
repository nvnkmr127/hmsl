@extends('layouts.print')

@section('title', 'Final Bill — ' . $bill->bill_number)

@section('content')

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
    .group-label-row td {
        background: #f8fafc;
        font-weight: 800;
        font-size: 7.5pt;
        color: #6366f1;
        padding: 6px 8px;
    }

    /* Summary Layout - Fully Redesigned */
    .final-row {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 30px;
        margin-top: 10px;
    }
    
    .payments-area h3 {
        font-size: 8pt;
        margin: 0 0 8px 0;
        color: #64748b;
        text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9;
        display: inline-block;
    }
    .payment-item {
        display: flex;
        justify-content: space-between;
        font-size: 8pt;
        padding: 4px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .payment-item:last-child { border: none; }
    
    .totals-area {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .total-line {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 9pt;
    }
    .total-line.grand {
        margin-top: 8px;
        padding-top: 10px;
        border-top: 2px solid #0f172a;
        font-weight: 900;
        font-size: 11pt;
        color: #0f172a;
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

<div class="header-layout">
    <div class="logo-box">
        <img src="{{ asset('images/hospital_logo.png') }}" alt="Logo">
    </div>
    <div class="hospital-info">
        <h1>{{ \App\Models\Setting::get('hospital_name', 'DWARAKAMAI HOSPITAL') }}</h1>
        <p>{{ \App\Models\Setting::get('hospital_address') }}, {{ \App\Models\Setting::get('hospital_city') }}</p>
        <p>Ph: {{ \App\Models\Setting::get('hospital_phone') }} | Email: {{ \App\Models\Setting::get('hospital_email') }}</p>
    </div>
    <div class="bill-title-meta">
        <h2>{{ $bill->admission_id ? 'FINAL BILL' : 'OP BILL' }}</h2>
        <div class="invoice-no">#{{ $bill->bill_number }}</div>
    </div>
</div>

<div class="patient-section">
    <div>
        <div class="info-row"><span class="info-label">Patient Name</span><span class="info-val">{{ $bill->patient->full_name }}</span></div>
        <div class="info-row"><span class="info-label">Age / Gender</span><span class="info-val">{{ $bill->patient->age }} / {{ $bill->patient->gender }}</span></div>
        <div class="info-row"><span class="info-label">UHID (MRN)</span><span class="info-val">{{ $bill->patient->uhid }}</span></div>
        <div class="info-row"><span class="info-label">Consultant</span><span class="info-val">{{ $bill->admission?->doctor?->full_name ?? $bill->consultation?->doctor?->full_name ?? 'MEDICAL TEAM' }}</span></div>
    </div>
    <div>
        <div class="info-row"><span class="info-label">Invoice Date</span><span class="info-val">{{ $bill->created_at->format('d M, Y h:i A') }}</span></div>
        @if($bill->admission)
            <div class="info-row"><span class="info-label">IP Number</span><span class="info-val">{{ $bill->admission->admission_number }}</span></div>
            <div class="info-row"><span class="info-label">Ward / Bed</span><span class="info-val">{{ $bill->admission->ward_name }}</span></div>
        @else
            <div class="info-row"><span class="info-label">Visit Type</span><span class="info-val">{{ strtoupper($bill->consultation?->visit_type ?? 'Out-Patient') }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">Contact</span><span class="info-val">{{ $bill->patient->phone ?? '—' }}</span></div>
    </div>
</div>

<table class="items-table">
    <thead>
        <tr>
            <th>Service Description</th>
            <th style="text-align: right; width: 80px;">Unit Rate</th>
            <th style="text-align: center; width: 40px;">Qty</th>
            <th style="text-align: right; width: 90px;">Total Amount</th>
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
            $groupedItems = $bill->items->groupBy(function($item) { return $item->item_type ?? 'Service'; });
        @endphp

        @foreach($groupedItems as $type => $items)
            <tr class="group-label-row">
                <td colspan="4">{{ $groupNames[$type] ?? strtoupper($type) }}</td>
            </tr>
            @foreach($items as $item)
                <tr>
                    <td style="font-weight: 500;">{{ str_replace('DR. DR.', 'DR.', strtoupper($item->item_name)) }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<div class="amount-in-words">
    RUPEES {{ strtoupper($bill->amount_in_words) }} ONLY
</div>

<div class="final-row" style="display: block;">
    <div class="totals-area" style="width: 200px; margin-left: auto;">
        <div class="total-line"><span>Sub Total</span><span>₹{{ number_format($bill->subtotal, 2) }}</span></div>
        <div class="total-line"><span>Total Discount</span><span>- ₹{{ number_format($bill->discount_amount, 2) }}</span></div>
        <div class="total-line"><span>Amount Paid</span><span>₹{{ number_format($bill->paid_amount, 2) }}</span></div>
        <div class="total-line grand">
            <span>Balance Due</span>
            <span>₹{{ number_format($bill->balance_amount, 2) }}</span>
        </div>
    </div>
</div>

<div class="footer-signature">
    <div class="print-meta">
        Printed by: {{ strtoupper($bill->creator?->name ?? 'Admin') }}<br>
        Date: {{ now()->format('d/m/Y H:i:s') }}
    </div>
    <div class="signature-placeholder">
        <div class="sig-line">Authorized Signatory</div>
    </div>
</div>

@endsection
