@extends('layouts.print')

@section('title', 'OPD Slip - ' . $consultation->patient->full_name)

@section('content')
    <style>
        /* Ensure the container has no padding for exact positioning */
        .print-container {
            padding: 0 !important;
            margin: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
        }

        .letterhead-data {
            position: relative;
            width: 100%;
            height: 100%;
            font-family: 'Outfit', sans-serif;
            color: #000;
        }

        .field {
            position: absolute;
            font-size: 12pt;
            font-weight: 600;
            white-space: nowrap;
        }

        /* LINE 1: Name and Date */
        /* Adjust 'top' to move both Name and Date up/down */
        .pos-name {
            top: 4.3cm;
            left: 2.5cm;
            font-size: 13pt;
        }

        .pos-date {
            top: 4.3cm;
            left: 16.5cm;
        }

        /* LINE 2: Vitals and Validity */
        /* Adjust 'top' to move the entire second row up/down */
        .pos-age {
            top: 5.5cm;
            left: 1.8cm;
        }

        .pos-gender {
            top: 5.5cm;
            left: 5.3cm;
        }

        .pos-weight {
            top: 5.5cm;
            left: 9.3cm;
        }

        .pos-temp {
            top: 5.5cm;
            left: 12.5cm;
        }

        .pos-valid {
            top: 5.5cm;
            left: 18.0cm;
        }

        /* LINE 3: Financials */
        .pos-fee {
            top: 6.2cm;
            left: 17.0cm;
            font-size: 10pt;
            color: #333;
        }

        /* UHID styling beside name */
        .uhid-label {
            font-size: 10pt;
            font-weight: 400;
            margin-left: 12px;
            color: #444;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .print-container {
                padding: 0 !important;
            }
        }
    </style>

    <div class="letterhead-data">
        {{-- LINE 1 --}}
        <div class="field pos-name" style="display: flex; align-items: center; gap: 15px;">
            <div>
                {{ trim($consultation->patient->first_name . ' ' . $consultation->patient->last_name) ?: 'NAME NOT FOUND' }}
                <span class="uhid-label">(UHID: {{ $consultation->patient->uhid }})</span>
            </div>
            @if(\App\Models\Setting::get('enable_barcodes', false))
            <div style="margin-top: -5px;">
                {!! \App\Helpers\BarcodeHelper::generate($consultation->patient->uhid, 'TYPE_CODE_128', 1, 25) !!}
            </div>
            @endif
        </div>

        <div class="field pos-date">
            {{ $consultation->consultation_date->format('d/m/Y') }} <span
                style="font-size: 9pt; font-weight: 400; color: #666; margin-left: 5px;">{{ $consultation->created_at->format('h:i A') }}</span>
        </div>

        {{-- LINE 2 --}}
        <div class="field pos-age">
            {{ $consultation->patient->age }}
        </div>

        <div class="field pos-gender">
            {{ $consultation->patient->gender }}
        </div>

        <div class="field pos-weight">
            {{ $consultation->weight ? $consultation->weight . ' kg' : '--' }}
        </div>

        <div class="field pos-temp">
            {{ $consultation->temperature ? $consultation->temperature . ' °F' : '--' }}
        </div>

        <div class="field pos-valid">
            {{ $consultation->valid_upto?->format('d/m/Y') ?? '--' }}
        </div>

        {{-- LINE 3 --}}
        <div class="field pos-fee">
            @php
                $isNewbornReview = false;
                if ($consultation->patient->date_of_birth) {
                    $days = \Carbon\Carbon::parse($consultation->patient->date_of_birth)->startOfDay()->diffInDays($consultation->consultation_date->startOfDay());
                    $isNewbornReview = $days <= 7;
                }
            @endphp

            @if($isNewbornReview || $consultation->fee <= 0)
                REVIEW VISIT
            @else
                Paid: ₹{{ number_format($consultation->fee, 0) }} ({{ strtoupper($consultation->payment_method) }})
            @endif
        </div>
    </div>

@endsection