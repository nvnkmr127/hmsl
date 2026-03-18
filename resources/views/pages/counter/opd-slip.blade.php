@extends('layouts.print')

@section('title', 'OPD Slip - ' . $consultation->patient->full_name)

@section('content')
{{-- 1. Top blank (letterhead area) --}}
<div style="height: 7cm;"></div>

{{-- 3. Printed section (Condened 2 Rows) --}}
<div class="content"
    style="font-family: 'Courier New', Courier, monospace; font-size: 10pt; border-bottom: 1px dashed #000; padding-bottom: 15px;">

    {{-- Row 1: Identity --}}
    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
        <div style="width: 70%;">
            NAME: <strong style="font-size: 12pt; text-transform: uppercase;">
                {{ trim($consultation->patient->first_name) ?: ($consultation->patient->mother_name ?: 'NO NAME') }}
            </strong>
            <span style="margin-left: 15px; color: #666;">UHID: <strong style="color: #000;">{{ $consultation->patient->uhid }}</strong></span>
        </div>
        <div style="width: 25%; text-align: right;">
            DATE: <strong>{{ $consultation->consultation_date->format('d/m/Y') }}</strong>
        </div>
    </div>

    {{-- Row 2: Clinical & Billing --}}
    <div style="display: flex; justify-content: space-between; font-size: 9pt;">
        <div style="width: 30%;">
            AGE/SEX: <strong>{{ $consultation->patient->age }} / {{ substr($consultation->patient->gender, 0, 1) }}</strong>
        </div>
        <div style="width: 35%; text-align: center;">
            VITALS: <strong>{{ $consultation->weight ?? '--' }}kg | {{ $consultation->temperature ?? '--' }}°F</strong>
        </div>
        <div style="width: 35%; text-align: right;">
            CHARGES: <strong style="font-size: 11pt;">₹{{ number_format($consultation->fee, 0) }}</strong>
            <span style="margin-left: 10px; font-weight: bold; font-size: 8pt; border: 1px solid #000; padding: 1px 4px;">{{ strtoupper($consultation->payment_method) }}</span>
        </div>
    </div>

</div>

{{-- 4. Empty space for doctor writing --}}
<div style="margin-top: 20px; font-style: italic; color: #eee; font-size: 30pt; font-weight: 900; opacity: 0.5;">
    Rx
</div>

<div class="footer" style="margin-top: 20px;">
    <p style="font-size:7pt; color:#64748b; margin:0">
        Valid for follow-up within 7 days (Until: {{ $consultation->valid_upto->format('d/m/Y') }})
    </p>
</div>
@endsection