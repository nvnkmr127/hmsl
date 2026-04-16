@extends('layouts.print')

@section('title', 'Invoice — ' . $bill->bill_number)

@section('content')

<style>
    /* B&W Printing Overrides */
    .hospital-logo { background: #000 !important; border-radius: 4px !important; }
    .header { border-bottom: 2px solid #000 !important; }
    h2 { color: #000 !important; }
    .badge { background: #fff !important; color: #000 !important; border: 1px solid #000 !important; border-radius: 4px; padding: 2px 8px; font-weight: bold; }
    .section-box { border: 1px solid #000 !important; padding: 15px !important; border-radius: 0 !important; background: transparent !important; }
    .table th { background: #eee !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
    .table td { border-bottom: 1px solid #ddd !important; color: #000 !important; }
    .total-box { border: 2px solid #000 !important; border-radius: 0 !important; background: transparent !important; }
    .total-title { color: #000 !important; }
    .footer-line { background: #000 !important; opacity: 1 !important; height: 1px !important; }
    * { color: #000 !important; }
</style>

<div class="header" style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
    <div class="hospital-identity">
        <div class="hospital-info">
            <h1 style="font-size: 24pt; font-weight: bold; margin: 0;">{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }}</h1>
            <p style="font-size: 10pt; margin: 2px 0;">{{ \App\Models\Setting::get('hospital_address', 'Hospital Main Road') }}</p>
            <p style="font-size: 10pt; margin: 2px 0;">Phone: {{ \App\Models\Setting::get('hospital_phone', '+91 1234567890') }} | Email: {{ \App\Models\Setting::get('hospital_email', 'contact@hospital.com') }}</p>
        </div>
    </div>
    <div class="text-right">
        <h2 style="margin:0; font-size:20pt; font-weight: bold;">INVOICE</h2>
        <p style="margin:4px 0; font-weight: bold; font-size: 12pt;">#{{ $bill->bill_number }}</p>
        <p style="margin:2px 0; font-size:10pt;">Date: {{ $bill->created_at->format('d/m/Y h:i:s A') }}</p>
        <div class="badge" style="margin-top: 5px;">{{ strtoupper($bill->payment_status) }}</div>
    </div>
</div>

<div class="content">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div class="section-box">
            <p style="font-size: 8pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; text-decoration: underline;">Patient Details</p>
            <p style="font-size: 14pt; font-weight: bold; margin: 0;">{{ $bill->patient->full_name }}</p>
            <div style="font-size: 10pt; margin-top: 5px;">
                <p>UHID: <strong>{{ $bill->patient->uhid ?? 'N/A' }}</strong></p>
                <p>Age/Gender: {{ $bill->patient->age }} / {{ $bill->patient->gender }}</p>
                <p>Phone: {{ $bill->patient->phone ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="section-box">
            <p style="font-size: 8pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; text-decoration: underline;">Visit Details</p>
            @if($bill->admission)
            <div style="font-size: 10pt; margin-top: 5px;">
                <p>Admission No: <strong>{{ $bill->admission->admission_number }}</strong></p>
                <p>Ward/Bed: {{ $bill->admission->bed?->ward?->name ?? '—' }} / {{ $bill->admission->bed?->bed_number ?? '—' }}</p>
                <p>Doctor: <strong>{{ $bill->admission->doctor?->full_name ?? '—' }}</strong></p>
                <p>Admitted: {{ $bill->admission->admission_date?->format('d/m/Y H:i') ?? '—' }}</p>
                <p>Discharged: {{ $bill->admission->discharge_date?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
            @elseif($bill->consultation)
            <div style="font-size: 10pt; margin-top: 5px;">
                <p>Doctor: <strong>{{ $bill->consultation->doctor?->full_name ?? 'Resident Medical Officer' }}</strong></p>
                <p>Department: {{ $bill->consultation->doctor?->department?->name ?? 'OPD' }}</p>
                <p>Token: #{{ str_pad($bill->consultation->token_number, 2, '0', STR_PAD_LEFT) }}</p>
                <p>Visit Date: {{ $bill->consultation->consultation_date->format('d/m/Y') }}</p>
            </div>
            @else
            <p style="font-size: 10pt; font-style: italic;">Miscellaneous Billing</p>
            @endif
        </div>
    </div>

    <div style="margin-bottom: 20px; font-size: 11pt; line-height: 1.6; border: 1px solid #000; padding: 12px;">
        Received with thanks from <strong>{{ $bill->patient->full_name }}</strong> a sum of <strong>Rs {{ number_format($bill->total_amount, 2) }}</strong> 
        ({{ ucfirst($bill->amount_in_words) }} only) regarding the services mentioned below.
    </div>

    <table class="table" style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 8px;">#</th>
                <th style="border: 1px solid #000; padding: 8px;">Description</th>
                <th style="border: 1px solid #000; padding: 8px;">Category</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center;">Qty</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right;">Unit Price</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $idx => $item)
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $item->item_name }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ $item->item_type }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $item->quantity }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
        <div class="total-box" style="width: 250px; padding: 15px; border: 2px solid #000;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Subtotal:</span>
                <span>₹{{ number_format($bill->subtotal, 2) }}</span>
            </div>
            @if($bill->discount_amount > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Discount:</span>
                <span>- ₹{{ number_format($bill->discount_amount, 2) }}</span>
            </div>
            @endif
            <div style="border-top: 1px solid #000; margin: 5px 0; padding-top: 5px; display: flex; justify-content: space-between; font-weight: bold; font-size: 14pt;">
                <span>TOTAL:</span>
                <span>₹{{ number_format($bill->total_amount, 2) }}</span>
            </div>
            <p style="font-size: 9pt; margin-top: 10px; text-align: center; border: 1px solid #ddd; padding: 2px;">Method: {{ $bill->payment_method }}</p>
        </div>
    </div>

    @if($bill->notes)
    <div style="margin-top: 20px; border: 1px dashed #000; padding: 10px;">
        <p style="font-size: 8pt; font-weight: bold; margin: 0 0 5px;">NOTES:</p>
        <p style="font-size: 10pt; margin: 0;">{{ $bill->notes }}</p>
    </div>
    @endif
</div>

<div class="footer" style="padding-top: 50px;">
    <div style="display: flex; justify-content: space-between;">
        <div style="text-align: center; width: 220px;">
            <p style="font-size: 8.5pt; font-weight: bold; margin-bottom: 40px;">Received By: {{ $bill->creator?->name ?? 'System' }}</p>
            <div style="border-top: 1px solid #000; margin-bottom: 5px;"></div>
            <p style="font-size: 9pt; font-weight: bold;">Authorized Signature</p>
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="margin-bottom: 50px;"></div>
            <div style="border-top: 1px solid #000; margin-bottom: 5px;"></div>
            <p style="font-size: 9pt;">Patient/Guardian Signature</p>
        </div>
    </div>
    <div style="margin-top: 30px; border-top: 1px solid #000; padding-top: 10px;">
        <p style="font-size: 8pt; text-align: center;">{{ \App\Models\Setting::get('invoice_footer', 'Computer generated invoice.') }}</p>
        <p style="font-size: 7pt; text-align: center; margin-top: 5px;">Printed on: {{ now()->format('d/m/Y h:i:s A') }}</p>
    </div>
</div>
@endsection
