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
</div>

<div class="footer">
    <p>{{ \App\Models\Setting::get('invoice_footer', 'Thank you for choosing our hospital. Get well soon!') }}</p>
    <p style="margin:4px 0;">Admission: {{ $admission->admission_number }} &nbsp;|&nbsp; Patient: {{ $admission->patient->uhid }}</p>
</div>
@endsection

