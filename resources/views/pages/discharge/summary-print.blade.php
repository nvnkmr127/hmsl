@extends('layouts.print')

@section('title', 'Discharge Summary — ' . $admission->admission_number)

@section('content')
<div class="header">
    <div class="hospital-info">
        <h1>{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }}</h1>
        <p>{{ \App\Models\Setting::get('hospital_tagline', '') }}</p>
        <p>{{ \App\Models\Setting::get('hospital_address', '') }}, {{ \App\Models\Setting::get('hospital_city', '') }}</p>
        <p>📞 {{ \App\Models\Setting::get('hospital_phone', '') }} &nbsp;|&nbsp; ✉ {{ \App\Models\Setting::get('hospital_email', '') }}</p>
    </div>
    <div style="text-align:right">
        <p style="font-size:18pt; font-weight:700; color:#4F46E5; margin:0">DISCHARGE SUMMARY</p>
        <p style="margin:4px 0"><strong>{{ $admission->admission_number }}</strong></p>
        <p style="color:#888; margin:2px 0">Printed: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>

<div class="content">
    <div style="display:flex; gap:40px; margin-bottom:24px;">
        <div style="flex:1; background:#f8f9ff; border-radius:8px; padding:16px;">
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Patient</p>
            <p style="font-weight:700; font-size:13pt; margin:0 0 4px;">{{ $admission->patient->full_name }}</p>
            <p style="color:#555; margin:2px 0">UHID: <strong>{{ $admission->patient->uhid }}</strong></p>
            <p style="color:#555; margin:2px 0">Phone: {{ $admission->patient->phone }}</p>
        </div>
        <div style="flex:1; background:#f8f9ff; border-radius:8px; padding:16px;">
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Admission</p>
            <p style="margin:2px 0">Ward: <strong>{{ $admission->bed?->ward?->name ?? '—' }}</strong></p>
            <p style="margin:2px 0">Bed: <strong>{{ $admission->bed?->bed_number ?? '—' }}</strong></p>
            <p style="margin:2px 0">Doctor: <strong>{{ $admission->doctor?->full_name ?? '—' }}</strong></p>
            <p style="margin:2px 0">Admitted: {{ $admission->admission_date?->format('d/m/Y H:i') ?? '—' }}</p>
            <p style="margin:2px 0">Discharged: {{ $admission->discharge_date?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
    </div>

    <div style="margin-top:18px;">
        <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Reason for Admission</p>
        <div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px;">
            <p style="margin:0; color:#111;">{{ $admission->reason_for_admission ?: '—' }}</p>
        </div>
    </div>

    <div style="margin-top:18px;">
        <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Discharge Notes</p>
        <div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px;">
            <p style="margin:0; color:#111; white-space:pre-line;">{{ $admission->notes ?: '—' }}</p>
        </div>
    </div>

    <div style="margin-top:18px; display:flex; gap:16px;">
        <div style="flex:1;">
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Vitals</p>
            @php $latestVital = $admission->vitals?->sortByDesc('created_at')->first(); @endphp
            <div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px;">
                @if($latestVital)
                    <p style="margin:0; color:#111;">Date: <strong>{{ $latestVital->created_at?->format('d/m/Y H:i') }}</strong></p>
                    <p style="margin:4px 0 0; color:#555;">Temp: {{ $latestVital->temperature ?? '—' }}</p>
                    <p style="margin:2px 0 0; color:#555;">Weight: {{ $latestVital->weight ?? '—' }}</p>
                    <p style="margin:2px 0 0; color:#555;">BP: {{ $latestVital->bp_systolic ?? '—' }}{{ $latestVital->bp_diastolic ? '/' . $latestVital->bp_diastolic : '' }}</p>
                    <p style="margin:2px 0 0; color:#555;">SpO2: {{ $latestVital->spo2 ?? '—' }}</p>
                @else
                    <p style="margin:0; color:#555;">No vitals recorded.</p>
                @endif
            </div>
        </div>
        <div style="flex:2;">
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Lab Orders</p>
            <div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px;">
                @if($admission->labOrders?->count())
                    <table style="width:100%; border-collapse:collapse; font-size:9pt;">
                        <thead>
                            <tr style="text-align:left; color:#6b7280;">
                                <th style="padding:6px 4px;">Test</th>
                                <th style="padding:6px 4px;">Status</th>
                                <th style="padding:6px 4px;">Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admission->labOrders as $o)
                                <tr>
                                    <td style="padding:6px 4px; border-top:1px solid #f0f0f0;">{{ $o->labTest?->name ?? '—' }}</td>
                                    <td style="padding:6px 4px; border-top:1px solid #f0f0f0;">{{ $o->status }}</td>
                                    <td style="padding:6px 4px; border-top:1px solid #f0f0f0;">{{ $o->completed_at ? \Illuminate\Support\Carbon::parse($o->completed_at)->format('d/m/Y H:i') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="margin:0; color:#555;">No lab orders.</p>
                @endif
            </div>
        </div>
    </div>

    <div style="margin-top:18px;">
        <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Medications</p>
        <div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px;">
            @php $dispensed = $admission->medications?->filter(fn ($m) => (bool) $m->is_dispensed) ?? collect(); @endphp
            @if($dispensed->count())
                @foreach($dispensed as $rx)
                    <p style="margin:0 0 6px; color:#111;">Dispensed: <strong>{{ $rx->dispensed_at?->format('d/m/Y H:i') ?? '—' }}</strong></p>
                    @php $meds = is_array($rx->medicines) ? $rx->medicines : []; @endphp
                    @if(count($meds))
                        <ul style="margin:0 0 10px 18px; padding:0; color:#111;">
                            @foreach($meds as $m)
                                <li style="margin:2px 0;">
                                    <strong>{{ $m['name'] ?? 'Medicine' }}</strong>
                                    @if(isset($m['qty'])) · Qty {{ $m['qty'] }} @endif
                                    @if(isset($m['dose']) && $m['dose']) · {{ $m['dose'] }} @endif
                                    @if(isset($m['frequency']) && $m['frequency']) · {{ $m['frequency'] }} @endif
                                    @if(isset($m['duration']) && $m['duration']) · {{ $m['duration'] }} @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p style="margin:0 0 10px; color:#555;">No medicines listed.</p>
                    @endif
                @endforeach
            @else
                <p style="margin:0; color:#555;">No dispensed medications.</p>
            @endif
        </div>
    </div>

    @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id') && $admission->finalBill)
    <div style="margin-top:18px;">
        <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px">Final Bill</p>
        <div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px; display:flex; justify-content:space-between; gap: 20px;">
            <div>
                <p style="margin:0; color:#111;">Bill No: <strong>{{ $admission->finalBill->bill_number }}</strong></p>
                <p style="margin:4px 0 0; color:#555;">Status: {{ strtoupper($admission->finalBill->payment_status) }}</p>
                <p style="margin:4px 0 0; color:#555;">Paid: ₹{{ number_format((float) $admission->finalBill->paid_amount, 2) }} &nbsp;|&nbsp; Due: ₹{{ number_format(max(0, (float) $admission->finalBill->balance_amount), 2) }}</p>
            </div>
            <div style="text-align:right;">
                <p style="margin:0; color:#555;">Total</p>
                <p style="margin:2px 0 0; font-size:14pt; font-weight:800; color:#111;">₹{{ number_format($admission->finalBill->total_amount, 2) }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="footer">
    <p>{{ \App\Models\Setting::get('invoice_footer', 'Thank you for choosing our hospital. Get well soon!') }}</p>
    <p style="margin:4px 0;">Admission: {{ $admission->admission_number }} &nbsp;|&nbsp; Patient: {{ $admission->patient->uhid }}</p>
</div>
@endsection
