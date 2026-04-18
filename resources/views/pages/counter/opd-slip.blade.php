@extends('layouts.print')

@section('title', 'OPD Slip - ' . $consultation->patient->full_name)

@section('content')
{{-- 1. Fixed Top Margin for Letterhead (7cm as requested) --}}
<div style="height: 7cm; width: 100%;"></div>

@php
    $growthService = app(\App\Services\GrowthChartService::class);
    $growthData = $growthService->getGrowthStatus($consultation->patient, $consultation->weight, $consultation->height);
@endphp

{{-- 2. Slip Content - Compact Row Layout --}}
<div class="op-slip" style="font-family: 'Courier New', Courier, monospace; border-top: 1px dashed #000; border-bottom: 2px solid #000; padding: 15px 0;">
    
    {{-- ROW 1: Name & Timeline --}}
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
        <div style="width: 50%;">
            <span style="font-size: 8pt; color: #666; display: block; margin-bottom: 2px;">PATIENT NAME</span>
            <strong style="font-size: 14pt; text-transform: uppercase; letter-spacing: -0.02em;">
                {{ trim($consultation->patient->first_name) ?: ($consultation->patient->mother_name ?: 'NAME NOT FOUND') }}
            </strong>
            <span style="font-size: 9pt; color: #666; margin-left: 8px;">(UHID: {{ $consultation->patient->uhid }})</span>
        </div>
        <div style="width: 50%; text-align: right; font-size: 9pt;">
            <div style="margin-bottom: 3px;">DATE: <strong>{{ $consultation->consultation_date->format('d/m/Y') }}</strong> <span style="font-size: 8pt; color: #666; margin-left: 5px;">{{ $consultation->created_at->format('h:i A') }}</span></div>
            <div style="font-size: 8pt; color: #666;">VALID UPTO: <strong>{{ $consultation->valid_upto?->format('d/m/Y') ?? '—' }}</strong></div>
        </div>
    </div>

    {{-- ROW 2: Clinical Vitals & Financials --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 10px; border-top: 1px solid #eee;">
        <div style="width: 22%;">
            <span style="font-size: 7pt; color: #666; display: block; margin-bottom: 2px;">AGE / SEX</span>
            <strong style="font-size: 10pt;">{{ $consultation->patient->age }} / {{ $consultation->patient->gender }}</strong>
        </div>
        
        <div style="width: 56%; text-align: center; background: #fafafa; padding: 6px 15px; border-radius: 4px; display: flex; justify-content: center; gap: 15px;">
            <div>
                <span style="font-size: 7pt; color: #666; display: block;">WEIGHT</span>
                <strong style="font-size: 10pt;">{{ $consultation->weight ?? '--' }} kg</strong>
                @if($growthData && $growthData['weight']['expected_value'] != 'N/A')
                    <div style="font-size: 6.5pt; color: #4338ca; font-weight: bold; margin-top: 1px;">Exp: {{ $growthData['weight']['expected_value'] }}kg</div>
                @endif
            </div>
            <div style="width: 1px; height: 18px; background: #ddd; margin: 4px 0;"></div>
            <div>
                <span style="font-size: 7pt; color: #666; display: block;">HEIGHT</span>
                <strong style="font-size: 10pt;">{{ $consultation->height ?? '--' }} cm</strong>
                @if($growthData && $growthData['height']['expected_value'] != 'N/A')
                    <div style="font-size: 6.5pt; color: #4338ca; font-weight: bold; margin-top: 1px;">Exp: {{ $growthData['height']['expected_value'] }}cm</div>
                @endif
            </div>
            <div style="width: 1px; height: 18px; background: #ddd; margin: 4px 0;"></div>
            <div>
                <span style="font-size: 7pt; color: #666; display: block;">TEMP</span>
                <strong style="font-size: 10pt;">{{ $consultation->temperature ?? '--' }} °F</strong>
            </div>
        </div>

        <div style="width: 22%; text-align: right;">
            <span style="font-size: 7pt; color: #666; display: block; margin-bottom: 2px;">FEE</span>
            <strong style="font-size: 11pt;">₹{{ number_format($consultation->fee, 0) }}</strong>
            <span style="margin-left: 2px; padding: 1px 3px; background: #000; color: #fff; font-size: 6.5pt; font-weight: 900; vertical-align: middle;">{{ strtoupper($consultation->payment_method) }}</span>
        </div>
    </div>

</div>


{{-- 4. Prescription Space Indicator --}}
<div style="margin-top: 30px; font-weight: 900; font-size: 40pt; color: #ddd; opacity: 0.3; font-style: italic; font-family: 'Courier New', Courier, monospace;">
    Rx
</div>

@endsection
