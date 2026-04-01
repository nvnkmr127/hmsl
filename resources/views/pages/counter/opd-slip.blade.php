@extends('layouts.print')

@section('title', 'OPD Slip - ' . $consultation->patient->full_name)

@section('content')
{{-- 1. Fixed Top Margin for Letterhead (7cm as requested) --}}
<div style="height: 7cm; width: 100%;"></div>

{{-- 2. Slip Content - Compact 2-Row Layout --}}
<div class="op-slip" style="font-family: 'Courier New', Courier, monospace; border-top: 1px dashed #000; border-bottom: 2px solid #000; padding: 15px 0;">
    
    {{-- ROW 1: Identity & Timeline --}}
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 15px;">
        <div style="width: 60%;">
            NAME: <strong style="font-size: 13pt; text-transform: uppercase;">
                {{ trim($consultation->patient->first_name) ?: ($consultation->patient->mother_name ?: 'NAME NOT FOUND') }}
            </strong>
            <span style="font-size: 10pt; margin-left: 10px;">(UHID: {{ $consultation->patient->uhid }})</span>
        </div>
        <div style="width: 40%; text-align: right; font-size: 9pt; white-space: nowrap;">
            DATE: <strong>{{ $consultation->consultation_date->format('d/m/y') }}</strong> | 
            VALID: <strong>{{ $consultation->valid_upto?->format('d/m/y') ?? '—' }}</strong>
        </div>
    </div>

    {{-- ROW 2: Physicals, Fees, and Status --}}
    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 10pt;">
        <div style="width: 30%;">
            AGE/SEX: <strong>{{ $consultation->patient->age }} / {{ $consultation->patient->gender }}</strong>
        </div>
        <div style="width: 35%; text-align: center;">
            WT: <strong>{{ $consultation->weight ?? '--' }} kg</strong> | 
            TEMP: <strong>{{ $consultation->temperature ?? '--' }} °F</strong>
        </div>
        <div style="width: 35%; text-align: right;">
            FEE: <strong style="font-size: 11pt;">₹{{ number_format($consultation->fee, 0) }}</strong>
            <span style="margin-left: 5px; padding: 2px 6px; border: 1px solid #333; font-size: 8pt; font-weight: 900;">{{ strtoupper($consultation->payment_method) }}</span>
        </div>
    </div>

</div>

{{-- 3. Prescription Space Indicator --}}
<div style="margin-top: 30px; font-weight: 900; font-size: 40pt; color: #ddd; opacity: 0.3; font-style: italic;">
    Rx
</div>

@endsection
