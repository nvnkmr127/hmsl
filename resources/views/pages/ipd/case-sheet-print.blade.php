@extends('layouts.print')

@section('title', 'IPD Case Sheet — ' . $admission->admission_number)

@section('content')

    <style>
        /* =========================================================================
                                                                                                                                                                                                                                                                                                                   ALIGNMENT CONTROLS FOR PRE-PRINTED PAPER
                                                                                                                                                                                                                                                                                                                   Adjust these CSS variables to move the text up/down/left/right 
                                                                                                                                                                                                                                                                                                                   (Values are in millimeters or pixels. 'px' is recommended for fine tuning)
                                                                                                                                                                                                                                                                                                                   ========================================================================= */
        :root {
            /* TOP OFFSETS (Vertical alignment) */
            --top-ip-no: 226px;
            --top-uhid: 296px;
            /* 80px below IP NO */
            --top-room-no: 190px;
            --top-bed-no: 240px;
            --top-ref-dr: 358px;

            --top-patient-name: 453px;
            --top-mother-name: 453px;
            --top-father-name: 493px;
            --top-age: 493px;
            --top-sex: 493px;
            --top-weight: 493px;
            --top-address: 528px;
            --top-mobile: 563px;

            --top-date-admission: 660px;
            --top-ward-room: 753px;

            /* LEFT OFFSETS (Horizontal alignment) */
            --left-ip-no: 90px;
            --left-uhid: 90px;
            --left-room-no: 695px;
            --left-bed-no: 689px;
            --left-ref-dr: 115px;

            --left-patient-name: 171px;
            --left-mother-name: 535px;
            --left-father-name: 171px;
            --left-age: 390px;
            --left-sex: 525px;
            --left-weight: 655px;
            --left-address: 142px;
            --left-mobile: 142px;

            --left-date-admission: 250px;
            --left-ward-room: 200px;

            /* FONT SIZES */
            --font-size-general: 11pt;
            --font-size-ip: 12pt;
        }

        @page {
            size: A4 portrait;
            margin: 0 !important;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                overflow: hidden !important;
            }

            .print-container {
                width: 210mm !important;
                height: 297mm !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                overflow: hidden !important;
            }

            .page-wrapper {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                overflow: hidden !important;
            }

            #bg-form-image {
                width: 210mm !important;
                height: 297mm !important;
                object-fit: fill !important;
            }
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .print-field {
            position: absolute;
            font-size: var(--font-size-general);
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            white-space: nowrap;
            z-index: 50;
            /* Ensure text is strictly above the image */
        }

        /* SPECIFIC FIELD POSITIONS */
        #f-ip-no {
            top: var(--top-ip-no);
            left: var(--left-ip-no);
            font-size: var(--font-size-ip);
            letter-spacing: 2px;
        }

        #f-uhid {
            top: var(--top-uhid);
            left: var(--left-uhid);
            font-size: var(--font-size-ip);
            letter-spacing: 2px;
        }

        #f-room-no {
            top: var(--top-room-no);
            left: var(--left-room-no);
        }

        #f-bed-no {
            top: var(--top-bed-no);
            left: var(--left-bed-no);
        }

        #f-ref-dr {
            top: var(--top-ref-dr);
            left: var(--left-ref-dr);
        }

        #f-patient-name {
            top: var(--top-patient-name);
            left: var(--left-patient-name);
        }

        #f-mother-name {
            top: var(--top-mother-name);
            left: var(--left-mother-name);
        }

        #f-father-name {
            top: var(--top-father-name);
            left: var(--left-father-name);
        }

        #f-age {
            top: var(--top-age);
            left: var(--left-age);
        }

        #f-sex {
            top: var(--top-sex);
            left: var(--left-sex);
        }

        #f-weight {
            top: var(--top-weight);
            left: var(--left-weight);
        }

        #f-address {
            top: var(--top-address);
            left: var(--left-address);
        }

        #f-mobile {
            top: var(--top-mobile);
            left: var(--left-mobile);
        }

        #f-date-admission {
            top: var(--top-date-admission);
            left: var(--left-date-admission);
        }

        #f-ward-room {
            top: var(--top-ward-room);
            left: var(--left-ward-room);
        }

        /* Helper container for development view (adds a border to simulate A4 paper on screen) */
        .page-wrapper {
            position: relative;
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: white;
        }

        @media screen {
            .page-wrapper {
                border: 1px solid #ccc;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
            }

            /* Optional: You can put the image URL here temporarily as background to visually align elements on screen */
            /* .page-wrapper { background-image: url('/images/pre_printed_form.png'); background-size: cover; opacity: 0.8; } */
        }
    </style>

    <div class="page-wrapper">
        @php
            $months = 999;
            if ($admission->patient->date_of_birth) {
                $months = \Carbon\Carbon::parse($admission->patient->date_of_birth)->diffInMonths(\Carbon\Carbon::now());
            }
            $bgImage = $months <= 3 ? 'NICU_casesheet.png' : 'case_sheet.png';

            $wardName = $admission->bed->ward->name ?? '';
            $bedNo = $admission->bed->bed_number ?? '';

            $replacements = [
                'general ward' => 'GW',
                'special (single) room' => 'SR',
                'special room with ac' => 'SRAC',
                'sharing room' => 'SHR'
            ];

            $wardShort = $wardName;
            $bedShort = $bedNo;

            foreach ($replacements as $search => $replace) {
                // Replace case-insensitively
                $wardShort = str_ireplace($search, $replace, $wardShort);
                $bedShort = str_ireplace($search, $replace, $bedShort);
            }
        @endphp

        <!-- Printed Form Background -->
        <img id="bg-form-image" src="{{ asset('images/' . $bgImage) }}" alt="Case Sheet Form"
            style="position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; z-index: 10; display: block; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
        <!-- Header fields -->
        <div class="print-field" id="f-ip-no">{{ substr($admission->admission_number, -4) }}</div>
        <div class="print-field" id="f-uhid">UHID : {{ $admission->patient->uhid }}</div>
        <div class="print-field" id="f-room-no">{{ $wardShort }}</div>
        <div class="print-field" id="f-bed-no">{{ $bedShort }}</div>
        <div class="print-field" id="f-ref-dr">{{ $admission->doctor->full_name ?? '' }}</div>

        <!-- Patient Information fields -->
        <div class="print-field" id="f-patient-name">{{ $admission->patient->full_name }}</div>
        <div class="print-field" id="f-mother-name">{{ $admission->patient->mother_name ?? '' }}</div>
        <div class="print-field" id="f-father-name">{{ $admission->patient->father_name ?? '' }}</div>
        <div class="print-field" id="f-age">{{ $admission->patient->age }}</div>
        <div class="print-field" id="f-sex">{{ substr($admission->patient->gender, 0, 1) }}</div>
        <div class="print-field" id="f-weight">
            {{ $admission->ipdVitals->first()?->weight ? $admission->ipdVitals->first()->weight . ' kg' : '' }}
        </div>
        <div class="print-field" id="f-address">{{ Str::limit($admission->patient->address, 60) }}</div>
        <div class="print-field" id="f-mobile">{{ $admission->patient->phone }}</div>

        <!-- Admission Details fields -->
        <div class="print-field" id="f-date-admission">{{ $admission->admission_date->format('d/m/Y h:i A') }}</div>
        <div class="print-field" id="f-ward-room">{{ $wardShort }} / {{ $bedShort }}</div>
    </div>

@endsection