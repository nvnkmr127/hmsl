@extends('layouts.print')

@section('title', 'Invoice — ' . $bill->bill_number)

@section('content')
<div class="header">
    <div class="hospital-info">
        <h1>{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }}</h1>
        <p>{{ \App\Models\Setting::get('hospital_tagline', '') }}</p>
        <p>{{ \App\Models\Setting::get('hospital_address', '') }}, {{ \App\Models\Setting::get('hospital_city', '') }}</p>
        <p>📞 {{ \App\Models\Setting::get('hospital_phone', '') }} &nbsp;|&nbsp; ✉ {{ \App\Models\Setting::get('hospital_email', '') }}</p>
    </div>
    <div style="text-align:right">
        <p style="font-size:18pt; font-weight:700; color:#4F46E5; margin:0">INVOICE</p>
        <p style="margin:4px 0"><strong>{{ $bill->bill_number }}</strong></p>
        <p style="color:#888; margin:2px 0">Date: {{ $bill->created_at->format('d/m/Y H:i') }}</p>
        <span style="display:inline-block; padding:4px 14px; border-radius:20px; font-size:9pt; font-weight:700;
            background:{{ $bill->payment_status==='Paid' ? '#d1fae5' : '#fee2e2' }};
            color:{{ $bill->payment_status==='Paid' ? '#065f46' : '#991b1b' }}">
            {{ strtoupper($bill->payment_status) }}
        </span>
    </div>
</div>

<div class="content">
    {{-- Patient & Visit Info --}}
    <div style="display:flex; gap:40px; margin-bottom:24px;">
        <div style="flex:1; background:#f8f9ff; border-radius:8px; padding:16px;">
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Bill To</p>
            <p style="font-weight:700; font-size:13pt; margin:0 0 4px;">{{ $bill->patient->full_name }}</p>
            <p style="color:#555; margin:2px 0">UHID: <strong>{{ $bill->patient->uhid }}</strong></p>
            <p style="color:#555; margin:2px 0">Phone: {{ $bill->patient->phone }}</p>
            @if($bill->patient->address)
                <p style="color:#555; margin:2px 0">{{ $bill->patient->address }}{{ $bill->patient->city ? ', ' . $bill->patient->city : '' }}</p>
            @endif
        </div>
        <div style="flex:1; background:#f8f9ff; border-radius:8px; padding:16px;">
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Consultation Details</p>
            @if($bill->consultation)
                <p style="margin:2px 0">Doctor: <strong>{{ $bill->consultation->doctor?->full_name ?? '—' }}</strong></p>
                <p style="margin:2px 0">Dept: {{ $bill->consultation->doctor?->department?->name ?? '—' }}</p>
                <p style="margin:2px 0">Date: {{ $bill->consultation->consultation_date->format('d/m/Y') }}</p>
                <p style="margin:2px 0">Token: #{{ $bill->consultation->token_number }}</p>
            @else
                <p style="color:#888;">Walk-in / Manual Bill</p>
            @endif
        </div>
    </div>

    {{-- Line Items --}}
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Type</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Unit Price</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td style="color:#888; font-size:9pt">{{ $item->item_type }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:right"><strong>₹{{ number_format($item->total_price, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div style="display:flex; justify-content:flex-end; margin-top:8px;">
        <div style="width:280px;">
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid #eee;">
                <span style="color:#555;">Subtotal</span>
                <span>₹{{ number_format($bill->subtotal, 2) }}</span>
            </div>
            @if($bill->discount_amount > 0)
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid #eee; color:#059669;">
                <span>Discount (–)</span>
                <span>₹{{ number_format($bill->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($bill->tax_amount > 0)
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid #eee;">
                <span>Tax (+)</span>
                <span>₹{{ number_format($bill->tax_amount, 2) }}</span>
            </div>
            @endif
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:3px double #4F46E5; margin-top:4px;">
                <span style="font-weight:700; font-size:13pt;">NET TOTAL</span>
                <span style="font-weight:700; font-size:15pt; color:#4F46E5;">₹{{ number_format($bill->total_amount, 2) }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0; background:#f0fdf4; border-radius:6px; padding:8px 12px;">
                <span style="color:#374151; font-size:9pt;">Payment Method</span>
                <span style="font-weight:700; color:#065f46;">{{ $bill->payment_method ?? 'Cash' }}</span>
            </div>
        </div>
    </div>

    @if($bill->notes)
    <div style="margin-top:24px; background:#fffbeb; border:1px solid #fed7aa; border-radius:8px; padding:12px;">
        <p style="font-size:8pt; font-weight:700; color:#92400e; text-transform:uppercase; margin:0 0 4px;">Notes</p>
        <p style="margin:0; color:#555;">{{ $bill->notes }}</p>
    </div>
    @endif
</div>

<div class="footer">
    <p>{{ \App\Models\Setting::get('invoice_footer', 'Thank you for choosing our hospital. Get well soon!') }}</p>
    <p style="margin:4px 0;">Generated: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; Bill: {{ $bill->bill_number }}</p>
</div>
@endsection
