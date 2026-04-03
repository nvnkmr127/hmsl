@extends('layouts.print')

@section('title', 'Invoice — ' . $bill->bill_number)

@section('content')

<div class="header">
    <div class="hospital-identity">
        <div class="hospital-logo">
            {{ substr(\App\Models\Setting::get('hospital_name', 'H'), 0, 1) }}
        </div>
        <div class="hospital-info">
            <h1>{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }}</h1>
            <p>{{ \App\Models\Setting::get('hospital_tagline', 'Excellence in Healthcare') }}</p>
            <p>{{ \App\Models\Setting::get('hospital_address', 'Hospital Main Road') }}</p>
            <p>Phone: {{ \App\Models\Setting::get('hospital_phone', '+91 1234567890') }} | Email: {{ \App\Models\Setting::get('hospital_email', 'contact@hospital.com') }}</p>
        </div>
    </div>
    <div class="text-right">
        <h2 style="margin:0; font-size:18pt; font-weight:900; color:#4f46e5; letter-spacing:0.05em;">INVOICE</h2>
        <p style="margin:4px 0; font-weight:700; font-size:11pt; color:#0f172a;">#{{ $bill->bill_number }}</p>
        <p style="margin:2px 0; font-size:9pt; color:#64748b; font-weight:600;">{{ $bill->created_at->format('d M Y, h:i A') }}</p>
        <div style="margin-top:8px; display:inline-block; padding:4px 12px; border-radius:8px; font-size:8pt; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; 
            background: {{ $bill->payment_status === 'Paid' ? '#f0fdf4' : '#fef2f2' }}; 
            color: {{ $bill->payment_status === 'Paid' ? '#166534' : '#991b1b' }};
            border: 1px solid {{ $bill->payment_status === 'Paid' ? '#dcfce7' : '#fee2e2' }};">
            {{ $bill->payment_status }}
        </div>
    </div>
</div>

<div class="content">
    <div style="display:grid; grid-template-cols: 1fr 1fr; gap:24px; margin-bottom:32px;">
        <div style="background:#f8fafc; padding:20px; border-radius:20px; border:1px solid #f1f5f9;">
            <p style="font-size:7.5pt; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.15em; margin-bottom:12px;">Bill To</p>
            <p style="font-size:13pt; font-weight:900; color:#0f172a; margin:0 0 4px;">{{ $bill->patient->full_name }}</p>
            <div style="display:flex; flex-direction:column; gap:2px; font-size:9.5pt; color:#475569; font-weight:500;">
                <p>UHID: <span style="font-weight:700; color:#0f172a;">{{ $bill->patient->uhid }}</span></p>
                <p>Phone: {{ $bill->patient->phone ?? 'N/A' }}</p>
                @if($bill->patient->address)
                <p style="margin-top:4px; line-height:1.2;">{{ $bill->patient->address }}</p>
                @endif
            </div>
        </div>
        <div style="background:#f8fafc; padding:20px; border-radius:20px; border:1px solid #f1f5f9;">
            <p style="font-size:7.5pt; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.15em; margin-bottom:12px;">Reference Information</p>
            @if($bill->consultation)
            <div style="display:flex; flex-direction:column; gap:4px; font-size:9.5pt; color:#475569; font-weight:500;">
                <p>Doctor: <span style="font-weight:700; color:#0f172a;">{{ $bill->consultation->doctor?->full_name ?? 'Resident Medical Officer' }}</span></p>
                <p>Department: {{ $bill->consultation->doctor?->department?->name ?? 'OPD' }}</p>
                <p>OP Token: #{{ str_pad($bill->consultation->token_number, 2, '0', STR_PAD_LEFT) }}</p>
                <p>Visit Date: {{ $bill->consultation->consultation_date->format('d M Y') }}</p>
            </div>
            @else
            <p style="font-size:10pt; color:#94a3b8; font-style:italic; margin-top:20px;">Miscellaneous Billing / Service Charges</p>
            @endif
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Service Description</th>
                <th style="width:100px;">Category</th>
                <th class="text-center" style="width:60px;">Qty</th>
                <th class="text-right" style="width:120px;">Unit Price</th>
                <th class="text-right" style="width:120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $idx => $item)
            <tr>
                <td style="color:#94a3b8; font-size:9pt; font-weight:700;">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <p style="font-weight:700; color:#0f172a; margin:0;">{{ $item->item_name }}</p>
                </td>
                <td style="font-size:8.5pt; font-weight:600; color:#64748b;">{{ $item->item_type }}</td>
                <td class="text-center" style="font-weight:600;">{{ $item->quantity }}</td>
                <td class="text-right" style="color:#475569;">₹{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right font-black" style="font-size:10.5pt; color:#0f172a;">₹{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display:flex; justify-content: space-between; align-items: flex-start; margin-top:24px;">
        <div style="width:50%;">
            @if($bill->notes)
            <div style="background:#fffbeb; border:1px solid #fef3c7; padding:16px; border-radius:16px;">
                <p style="font-size:7pt; font-weight:800; color:#b45309; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:4px;">Billing Notes</p>
                <p style="font-size:9pt; color:#78350f; line-height:1.4;">{{ $bill->notes }}</p>
            </div>
            @endif
        </div>
        <div style="width:300px; background:#f8fafc; border-radius:24px; padding:24px; border:1px solid #f1f5f9;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:10pt; color:#64748b; font-weight:600;">
                <span>Subtotal</span>
                <span style="color:#0f172a;">₹{{ number_format($bill->subtotal, 2) }}</span>
            </div>
            @if($bill->discount_amount > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:10pt; color:#059669; font-weight:600;">
                <span>Discount</span>
                <span>- ₹{{ number_format($bill->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($bill->tax_amount > 0)
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:10pt; color:#64748b; font-weight:600;">
                <span>Taxes</span>
                <span style="color:#0f172a;">+ ₹{{ number_format($bill->tax_amount, 2) }}</span>
            </div>
            @endif
            <div style="height:1px; background:#e2e8f0; margin:12px 0;"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:9pt; font-weight:800; color:#0f172a; text-transform:uppercase; letter-spacing:0.05em;">Total Payable</span>
                <span style="font-size:16pt; font-weight:900; color:#4f46e5;">₹{{ number_format($bill->total_amount, 2) }}</span>
            </div>
            <div style="margin-top:16px; padding:8px 12px; background:white; border:1px solid #e2e8f0; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:7.5pt; font-weight:800; color:#94a3b8; text-transform:uppercase;">Pay Method</span>
                <span style="font-size:9pt; font-weight:800; color:#0f172a;">{{ $bill->payment_method }}</span>
            </div>
        </div>
    </div>
</div>

<div class="footer" style="padding-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items: flex-end; margin-bottom:20px;">
        <div style="text-align:left;">
            <p style="font-size:7.5pt; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-bottom:40px;">Billing Executive</p>
            <div style="width:150px; border-bottom:1px solid #e2e8f0;"></div>
            <p style="font-size:8pt; font-weight:700; color:#64748b; margin-top:8px;">Authorized Signature</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:7.5pt; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-bottom:40px;">Patient/Guardian</p>
            <div style="width:150px; border-bottom:1px solid #e2e8f0;"></div>
            <p style="font-size:8pt; font-weight:700; color:#64748b; margin-top:8px;">Signature</p>
        </div>
    </div>
    <div style="background:#4f46e5; height:4px; border-radius:2px; margin-bottom:12px; opacity:0.1;"></div>
    <p style="font-weight:700; color:#475569; font-size:8.5pt;">{{ \App\Models\Setting::get('invoice_footer', 'This is a computer-generated document and does not require a physical signature for validity unless specified.') }}</p>
    <p style="margin-top:4px;">Printed on {{ date('d M Y, h:i A') }}</p>
</div>
@endsection

