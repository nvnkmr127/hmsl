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
        --top-ip-no: 165px;
        --top-room-no: 150px;
        --top-bed-no: 185px;
        --top-ref-dr: 285px;
        
        --top-patient-name: 375px;
        --top-mother-name: 375px;
        --top-father-name: 415px;
        --top-age: 415px;
        --top-sex: 415px;
        --top-weight: 415px;
        --top-address: 455px;
        --top-mobile: 495px;

        --top-date-admission: 570px;
        --top-ward-room: 650px;

        /* LEFT OFFSETS (Horizontal alignment) */
        --left-ip-no: 65px;
        --left-room-no: 680px;
        --left-bed-no: 680px;
        --left-ref-dr: 110px;
        
        --left-patient-name: 180px;
        --left-mother-name: 580px;
        --left-father-name: 180px;
        --left-age: 400px;
        --left-sex: 530px;
        --left-weight: 650px;
        --left-address: 140px;
        --left-mobile: 140px;

        --left-date-admission: 250px;
        --left-ward-room: 200px;
        
        /* FONT SIZES */
        --font-size-general: 13pt;
        --font-size-ip: 14pt;
    }

    @media print {
        @page { 
            size: A4 portrait; 
            margin: 0; /* Important: 0 margin for exact positioning on pre-printed form */
        }
        body { 
            margin: 0; 
            padding: 0; 
            background: transparent !important;
            -webkit-print-color-adjust: exact;
        }
        .print-container { 
            padding: 0 !important; 
            margin: 0 !important; 
            border: none !important;
            box-shadow: none !important;
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
    }

    /* SPECIFIC FIELD POSITIONS */
    #f-ip-no { top: var(--top-ip-no); left: var(--left-ip-no); font-size: var(--font-size-ip); letter-spacing: 2px; }
    #f-room-no { top: var(--top-room-no); left: var(--left-room-no); }
    #f-bed-no { top: var(--top-bed-no); left: var(--left-bed-no); }
    #f-ref-dr { top: var(--top-ref-dr); left: var(--left-ref-dr); }
    
    #f-patient-name { top: var(--top-patient-name); left: var(--left-patient-name); }
    #f-mother-name { top: var(--top-mother-name); left: var(--left-mother-name); }
    #f-father-name { top: var(--top-father-name); left: var(--left-father-name); }
    #f-age { top: var(--top-age); left: var(--left-age); }
    #f-sex { top: var(--top-sex); left: var(--left-sex); }
    #f-weight { top: var(--top-weight); left: var(--left-weight); }
    #f-address { top: var(--top-address); left: var(--left-address); }
    #f-mobile { top: var(--top-mobile); left: var(--left-mobile); }

    #f-date-admission { top: var(--top-date-admission); left: var(--left-date-admission); }
    #f-ward-room { top: var(--top-ward-room); left: var(--left-ward-room); }

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
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        /* Optional: You can put the image URL here temporarily as background to visually align elements on screen */
        /* .page-wrapper { background-image: url('/images/pre_printed_form.png'); background-size: cover; opacity: 0.8; } */
    }
</style>

<div class="page-wrapper">
    <!-- Header fields -->
    <div class="print-field" id="f-ip-no">{{ substr($admission->admission_number, -4) }}</div>
    <div class="print-field" id="f-room-no">{{ $admission->bed->ward->name ?? '' }}</div>
    <div class="print-field" id="f-bed-no">{{ $admission->bed->bed_number ?? '' }}</div>
    <div class="print-field" id="f-ref-dr">{{ $admission->doctor->full_name ?? '' }}</div>

    <!-- Patient Information fields -->
    <div class="print-field" id="f-patient-name">{{ $admission->patient->full_name }} (UHID: {{ $admission->patient->uhid }})</div>
    <div class="print-field" id="f-mother-name">{{ $admission->patient->mother_name ?? '' }}</div>
    <div class="print-field" id="f-father-name">{{ $admission->patient->father_name ?? '' }}</div>
    <div class="print-field" id="f-age">{{ $admission->patient->age }}</div>
    <div class="print-field" id="f-sex">{{ substr($admission->patient->gender, 0, 1) }}</div>
    <div class="print-field" id="f-weight">{{ $admission->ipdVitals->first()?->weight ? $admission->ipdVitals->first()->weight . ' kg' : '' }}</div>
    <div class="print-field" id="f-address">{{ Str::limit($admission->patient->address, 60) }}</div>
    <div class="print-field" id="f-mobile">{{ $admission->patient->phone }}</div>

    <!-- Admission Details fields -->
    <div class="print-field" id="f-date-admission">{{ $admission->admission_date->format('d/m/Y h:i A') }}</div>
    <div class="print-field" id="f-ward-room">{{ $admission->bed->ward->name ?? '' }} / {{ $admission->bed->bed_number ?? '' }}</div>
</div>

@endsection
