@extends('layouts.print')

@section('title', 'Final Bill — ' . $bill->bill_number)

@section('content')

<style>
    /* Print Aesthetics */
    @media print {
        body { font-size: 10pt; background: #fff !important; pointer-events: none; }
        .print-container { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .no-print { display: none !important; }
        .section-box { border: 1px solid #e2e8f0 !important; }
        .table th { background: #f8fafc !important; -webkit-print-color-adjust: exact; }
        tr { page-break-inside: avoid; }
        .group-header { page-break-after: avoid; }
    }

    body { font-family: 'Outfit', sans-serif; }
    .bill-header { border-bottom: 3px solid #0f172a; padding-bottom: 20px; margin-bottom: 30px; }
    .hospital-name { font-size: 26pt; font-weight: 900; color: #0f172a; margin: 0; line-height: 1; }
    .bill-type { font-size: 14pt; font-weight: 800; background: #0f172a; color: white !important; padding: 4px 12px; display: inline-block; margin-top: 10px; }
    
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
    .info-section h3 { font-size: 9pt; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px; }
    .info-row { display: flex; margin-bottom: 4px; font-size: 10pt; }
    .info-label { width: 120px; color: #64748b; font-weight: 500; }
    .info-value { font-weight: 700; color: #1e293b; }

    .summary-bar { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; display: flex; justify-content: space-between; margin-bottom: 30px; }
    .summary-item { text-align: center; }
    .summary-label { font-size: 8pt; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .summary-value { font-size: 11pt; font-weight: 800; color: #0f172a; }

    .group-header { background: #f1f5f9; padding: 6px 12px; font-weight: 800; font-size: 9pt; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; }
    
    .total-section { margin-top: 30px; display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
    .amount-in-words { font-style: italic; color: #64748b; font-size: 9pt; padding-top: 10px; }
    .total-table { width: 100%; border-collapse: collapse; }
    .total-table td { padding: 6px 0; }
    .total-table .label { color: #64748b; font-weight: 500; }
    .total-table .value { text-align: right; font-weight: 700; color: #1e293b; }
    .final-row { border-top: 2px solid #0f172a; margin-top: 8px; padding-top: 8px !important; }
    .final-amount { font-size: 18pt; font-weight: 900; color: #0f172a; }

    .footer-sections { margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 100px; }
    .signature-box { text-align: center; border-top: 1px solid #cbd5e1; padding-top: 8px; }
    .signature-label { font-size: 9pt; font-weight: 600; color: #475569; }

    .payment-badge { padding: 2px 8px; border-radius: 4px; font-size: 8pt; font-weight: 800; }
    .badge-paid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-pending { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="bill-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 class="hospital-name">{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }}</h1>
            <p style="margin: 4px 0; font-weight: 500; color: #64748b;">
                {{ \App\Models\Setting::get('hospital_address', 'Hospital Main Road') }}<br>
                Phone: {{ \App\Models\Setting::get('hospital_phone', '+91 1234567890') }} | Email: {{ \App\Models\Setting::get('hospital_email', 'contact@hospital.com') }}<br>
                @if($gst = \App\Models\Setting::get('hospital_gst'))
                    GSTIN: <strong>{{ $gst }}</strong>
                @endif
            </p>
            <div class="bill-type">
                @if($bill->admission_id)
                    FINAL INPATIENT BILL
                @else
                    OUTPATIENT BILL
                @endif
            </div>
        </div>
        <div style="text-align: right;">
            @if($logo = \App\Models\Setting::get('hospital_logo'))
                <img src="{{ Storage::url($logo) }}" alt="Logo" style="max-height: 80px; margin-bottom: 10px;">
            @else
                <div style="width: 60px; height: 60px; background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 24pt; border-radius: 12px; margin-left: auto; margin-bottom: 10px;">
                    H
                </div>
            @endif
        </div>
    </div>
</div>

<div class="summary-bar">
    <div class="summary-item">
        <div class="summary-label">Bill Number</div>
        <div class="summary-value">#{{ $bill->bill_number }}</div>
    </div>
    <div class="summary-item">
        <div class="summary-label">Bill Date</div>
        <div class="summary-value">{{ $bill->created_at->format('d/m/Y h:i A') }}</div>
    </div>
    <div class="summary-item">
        <div class="summary-label">Payment Status</div>
        <div class="summary-value">
            <span class="payment-badge {{ $bill->payment_status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                {{ strtoupper($bill->payment_status) }}
            </span>
        </div>
    </div>
    <div class="summary-item">
        <div class="summary-label">Payment Mode</div>
        <div class="summary-value">{{ $bill->payment_method ?? '—' }}</div>
    </div>
</div>

<div class="info-grid">
    <div class="info-section">
        <h3>Patient Information</h3>
        <div class="info-row"><span class="info-label">Patient Name:</span> <span class="info-value">{{ $bill->patient->full_name }}</span></div>
        <div class="info-row"><span class="info-label">Patient ID:</span> <span class="info-value">{{ $bill->patient->uhid ?? '—' }}</span></div>
        <div class="info-row"><span class="info-label">Age / Gender:</span> <span class="info-value">{{ $bill->patient->age }} / {{ $bill->patient->gender }}</span></div>
        <div class="info-row"><span class="info-label">Phone:</span> <span class="info-value">{{ $bill->patient->phone ?? '—' }}</span></div>
    </div>
    <div class="info-section">
        <h3>Visit Details</h3>
        @if($bill->admission)
            <div class="info-row"><span class="info-label">IP Number:</span> <span class="info-value">{{ $bill->admission->admission_number }}</span></div>
            <div class="info-row"><span class="info-label">Doctor:</span> <span class="info-value">{{ $bill->admission->doctor?->full_name ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Ward / Bed:</span> <span class="info-value">{{ $bill->admission->bed?->ward?->name ?? '—' }} / {{ $bill->admission->bed?->bed_number ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Adm. Date:</span> <span class="info-value">{{ $bill->admission->admission_date?->format('d/m/Y H:i') ?? '—' }}</span></div>
            @if($bill->admission->discharge_date)
                <div class="info-row"><span class="info-label">Disch. Date:</span> <span class="info-value">{{ $bill->admission->discharge_date?->format('d/m/Y H:i') ?? '—' }}</span></div>
            @endif
        @else
            <div class="info-row"><span class="info-label">OP Number:</span> <span class="info-value">{{ $bill->consultation->token_number ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Doctor:</span> <span class="info-value">{{ $bill->consultation->doctor?->full_name ?? 'Resident Medical Officer' }}</span></div>
            <div class="info-row"><span class="info-label">Department:</span> <span class="info-value">{{ $bill->consultation->doctor?->department?->name ?? 'OPD' }}</span></div>
            <div class="info-row"><span class="info-label">Visit Date:</span> <span class="info-value">{{ $bill->created_at->format('d/m/Y') }}</span></div>
        @endif
    </div>
</div>

<table class="table" style="width: 100%; border: 1px solid #e2e8f0;">
    <thead>
        <tr>
            <th style="width: 40px;">#</th>
            <th>Description</th>
            <th style="text-align: center; width: 60px;">Qty</th>
            <th style="text-align: right; width: 100px;">Rate</th>
            <th style="text-align: right; width: 120px;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $groupedItems = $bill->items->groupBy('item_type');
            $rowNum = 1;
        @endphp

        @foreach($groupedItems as $type => $items)
            <tr>
                <td colspan="5" class="group-header">{{ strtoupper($type) }}</td>
            </tr>
            @php
                // Aggregate identical items within the group
                $aggregatedItems = $items->groupBy(fn($item) => $item->item_name . '_' . $item->unit_price)
                    ->map(fn($subItems) => (object)[
                        'item_name' => $subItems->first()->item_name,
                        'unit_price' => (float)$subItems->first()->unit_price,
                        'quantity' => $subItems->sum('quantity'),
                        'total_price' => $subItems->sum('total_price'),
                        'created_at' => $subItems->first()->created_at
                    ]);
                $groupSubtotal = $items->sum('total_price');
            @endphp
            @foreach($aggregatedItems as $item)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $rowNum++ }}</td>
                    <td>
                        <div style="font-weight: 600;">{{ $item->item_name }}</div>
                        <div style="font-size: 8pt; color: #94a3b8;">{{ $item->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
            <tr style="background: #f8fafc;">
                <td colspan="4" style="text-align: right; font-size: 8.5pt; font-weight: 700; color: #64748b; padding-right: 14px;">SUBTOTAL {{ strtoupper($type) }}:</td>
                <td style="text-align: right; font-weight: 800; color: #475569;">{{ number_format($groupSubtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="total-section">
    <div class="amount-in-words">
        <strong>Amount in words:</strong><br>
        {{ ucfirst($bill->amount_in_words) }} Only
        
        @if($bill->notes)
            <div style="margin-top: 20px; border-left: 2px solid #e2e8f0; padding-left: 15px;">
                <span style="font-size: 8pt; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Medical Notes / Remarks:</span><br>
                <span style="font-style: normal; color: #475569;">{{ $bill->notes }}</span>
            </div>
        @endif
    </div>
    
    <div>
        <table class="total-table">
            <tr>
                <td class="label">Gross Total</td>
                <td class="value">₹{{ number_format($bill->subtotal, 2) }}</td>
            </tr>
            
            @if($bill->discount_amount > 0)
                @php $discount = $bill->discounts()->latest()->first(); @endphp
                <tr>
                    <td class="label">
                        Discount 
                        @if($discount && $discount->discount_value > 0)
                            ({{ $discount->discount_type === 'percentage' ? number_format($discount->discount_value, 0).'%' : 'Flat' }})
                        @endif
                    </td>
                    <td class="value" style="color: #b91c1c;">- ₹{{ number_format($bill->discount_amount, 2) }}</td>
                </tr>
                @if($discount && $discount->reason)
                    <tr>
                        <td colspan="2" style="font-size: 7.5pt; color: #94a3b8; text-align: right; font-style: italic;">
                            Reason: {{ $discount->reason }} (Auth: {{ $discount->doctor?->full_name ?? $discount->appliedBy->name }})
                        </td>
                    </tr>
                @endif
            @endif

            @if($bill->tax_amount > 0)
                <tr>
                    <td class="label">CGST ({{ (\App\Models\Setting::get('tax_percentage', 18) / 2) }}%)</td>
                    <td class="value">₹{{ number_format($bill->tax_amount / 2, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">SGST ({{ (\App\Models\Setting::get('tax_percentage', 18) / 2) }}%)</td>
                    <td class="value">₹{{ number_format($bill->tax_amount / 2, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td class="label">Paid Amount</td>
                <td class="value">₹{{ number_format($bill->paid_amount, 2) }}</td>
            </tr>

            @if($bill->balance_amount > 0)
                <tr>
                    <td class="label" style="color: #b91c1c;">Due Amount</td>
                    <td class="value" style="color: #b91c1c;">₹{{ number_format($bill->balance_amount, 2) }}</td>
                </tr>
            @endif

            <tr class="final-row">
                <td class="label" style="font-weight: 800; color: #0f172a;">NET PAYABLE</td>
                <td class="value final-amount">₹{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>
</div>

@if($bill->payments->count() > 1)
    <div style="margin-top: 30px;">
        <h3 style="font-size: 9pt; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">Payment History</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
            <thead>
                <tr style="color: #64748b; border-bottom: 1px solid #e2e8f0;">
                    <th style="text-align: left; padding: 4px 0;">Date</th>
                    <th style="text-align: left; padding: 4px 0;">Mode</th>
                    <th style="text-align: left; padding: 4px 0;">Reference</th>
                    <th style="text-align: right; padding: 4px 0;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->payments as $payment)
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <td style="padding: 4px 0;">{{ $payment->received_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 4px 0; text-transform: capitalize;">{{ $payment->method }}</td>
                        <td style="padding: 4px 0; color: #94a3b8;">{{ $payment->reference ?? '—' }}</td>
                        <td style="padding: 4px 0; text-align: right; font-weight: 600;">₹{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="footer-sections">
    <div class="signature-box">
        <div style="height: 60px;"></div>
        <div class="signature-label">Billing Executive</div>
        <div style="font-size: 8pt; color: #94a3b8; margin-top: 4px;">{{ $bill->creator?->name ?? 'System' }}</div>
    </div>
    <div class="signature-box">
        <div style="height: 60px;"></div>
        <div class="signature-label">Authorized Signatory</div>
        <div style="font-size: 8pt; color: #94a3b8; margin-top: 4px;">(Seal & Signature)</div>
    </div>
</div>

<div style="margin-top: 40px; border-top: 1px dashed #e2e8f0; padding-top: 15px; font-size: 8pt; color: #64748b; text-align: center;">
    <p style="margin: 0;">{{ \App\Models\Setting::get('invoice_footer', '1. This is a computer-generated bill and does not require a physical signature for validity. 2. Please keep this bill for future reference and insurance claims.') }}</p>
    <p style="margin: 5px 0 0;">Printed on: {{ now()->format('d/m/Y h:i:s A') }}</p>
</div>

@endsection
