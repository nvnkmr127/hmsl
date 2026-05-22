@extends('layouts.print')

@section('title', 'Final IP Bill — ' . $bill->bill_number)

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    @media print {
        @page { size: A5 portrait; margin: 6mm 5mm; }
        body { font-size: 8pt; background: #fff !important; color: #1e293b; font-family: 'Outfit', sans-serif; }
        .print-container { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    }
    body { font-family: 'Outfit', sans-serif; color: #000; line-height: 1.3; font-size: 8.5pt; }
    .header-layout { display: flex; align-items: center; gap: 15px; padding-bottom: 12px; border-bottom: 2px solid #000; margin-bottom: 15px; }
    .logo-box img { height: 60px; width: auto; filter: grayscale(100%); }
    .hospital-info { flex: 1; }
    .hospital-info h1 { margin: 0; font-size: 16pt; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: -0.01em; }
    .hospital-info p { margin: 1px 0; font-size: 7.5pt; font-weight: 600; color: #333; }
    .bill-title-meta { text-align: right; }
    .bill-title-meta h2 { margin: 0; font-size: 14pt; font-weight: 900; color: #000; }
    .bill-title-meta .invoice-no { font-weight: 800; font-size: 9pt; color: #000; }

    .patient-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; padding: 0 5px; }
    .info-row { display: flex; margin-bottom: 4px; border-bottom: 1px solid #ddd; padding-bottom: 2px; }
    .info-label { width: 100px; font-weight: 700; color: #444; font-size: 7.5pt; text-transform: uppercase; }
    .info-val { flex: 1; font-weight: 800; color: #000; text-transform: uppercase; }

    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .items-table th { text-align: left; padding: 8px; background: #000; color: #fff; font-size: 7.5pt; text-transform: uppercase; font-weight: 700; }
    .items-table td { padding: 7px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
    .group-label-row td { background: #f0f0f0; font-weight: 900; font-size: 7.5pt; color: #000; padding: 6px 8px; border-bottom: 1px solid #ccc; }

    .final-row { display: block; margin-top: 10px; }
    .totals-area { width: 200px; margin-left: auto; display: flex; flex-direction: column; gap: 4px; }
    .total-line { display: flex; justify-content: space-between; padding: 4px 0; font-size: 9pt; }
    .total-line.grand { margin-top: 8px; padding-top: 10px; border-top: 2px solid #000; font-weight: 900; font-size: 11pt; color: #000; }

    .amount-in-words { margin-top: 15px; padding: 8px; background: #f0f0f0; border-radius: 4px; font-style: italic; font-weight: 700; color: #000; font-size: 7.5pt; border: 1px solid #ccc; }
    .footer-signature { margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
    .signature-placeholder { text-align: center; width: 160px; }
    .sig-line { border-top: 1.5px solid #000; margin-top: 35px; padding-top: 4px; font-weight: 800; font-size: 8pt; text-transform: uppercase; }
    .print-meta { font-size: 6.5pt; color: #666; }
</style>

<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
    <img src="{{ asset('images/DW_header.png') }}" alt="Hospital Header" style="max-width: 100%; height: auto; max-height: 90px; object-fit: contain;">
</div>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 15px;">
    <h2 style="margin: 0; font-size: 14pt; font-weight: 900; text-decoration: underline; color: #000;">FINAL BILL</h2>
    <div class="invoice-no" style="font-weight: 800; font-size: 9pt; color: #000;">Invoice No: #{{ $bill->bill_number }}</div>
</div>

<div class="patient-section">
    <div>
        <div class="info-row"><span class="info-label">Patient Name</span><span class="info-val">{{ $bill->patient->full_name }}</span></div>
        <div class="info-row"><span class="info-label">Age / Gender</span><span class="info-val">{{ $bill->patient->age }} / {{ $bill->patient->gender }}</span></div>
        <div class="info-row"><span class="info-label">UHID (MRN)</span><span class="info-val">{{ $bill->patient->uhid }}</span></div>
        <div class="info-row"><span class="info-label">Consultant</span><span class="info-val">{{ $bill->admission?->doctor?->full_name ?? 'MEDICAL TEAM' }}</span></div>
    </div>
    <div>
        <div class="info-row"><span class="info-label">Invoice Date</span><span class="info-val">{{ $bill->created_at->format('d M, Y h:i A') }}</span></div>
        <div class="info-row"><span class="info-label">IP Number</span><span class="info-val">{{ $bill->admission?->admission_number }}</span></div>
        <div class="info-row"><span class="info-label">Ward / Bed</span><span class="info-val">{{ $bill->admission?->ward_name }}</span></div>
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
            $groupNames = ['Room' => 'ACCOMODATION', 'Procedure' => 'SURGERY', 'Lab' => 'INVESTIGATIONS', 'Medicine' => 'PHARMACY'];
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

@if($bill->admission && $bill->admission->labOrders->count() > 0)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 8.5pt; font-weight: 900; margin: 0 0 5px 0; border-bottom: 1.5px solid #000; padding-bottom: 2px; text-transform: uppercase;">Laboratory Investigations Details</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
            <thead>
                <tr style="border-bottom: 1px solid #ddd; background: #f9f9f9;">
                    <th style="text-align: left; padding: 4px; font-weight: 700;">Order #</th>
                    <th style="text-align: left; padding: 4px; font-weight: 700;">Investigation Name</th>
                    <th style="text-align: left; padding: 4px; font-weight: 700;">Date</th>
                    <th style="text-align: right; padding: 4px; font-weight: 700;">Charge (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->admission->labOrders as $order)
                    <tr style="border-bottom: 1px solid #f1f1f1;">
                        <td style="padding: 4px; font-family: monospace;">{{ $order->order_number }}</td>
                        <td style="padding: 4px; font-weight: 500;">{{ strtoupper($order->labTest?->name) }}</td>
                        <td style="padding: 4px;">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 4px; text-align: right; font-weight: 600;">{{ number_format($order->labTest?->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="amount-in-words">RUPEES {{ strtoupper($bill->amount_in_words) }} ONLY</div>

<div class="final-row">
    <div class="totals-area">
        <div class="total-line"><span>Sub Total</span><span>₹{{ number_format($bill->subtotal, 2) }}</span></div>
        <div class="total-line"><span>Total Discount</span><span>- ₹{{ number_format($bill->discount_amount, 2) }}</span></div>
        <div class="total-line"><span>Amount Paid</span><span>₹{{ number_format($bill->paid_amount, 2) }}</span></div>
        <div class="total-line grand"><span>Balance Due</span><span>₹{{ number_format($bill->balance_amount, 2) }}</span></div>
    </div>
</div>

<div class="footer-signature">
    <div class="print-meta">Printed by: {{ strtoupper($bill->creator?->name ?? 'Admin') }}<br>Date: {{ now()->format('d/m/Y H:i:s') }}</div>
    <div class="signature-placeholder"><div class="sig-line">Authorized Signatory</div></div>
</div>
@endsection
