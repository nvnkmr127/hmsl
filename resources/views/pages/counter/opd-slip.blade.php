@extends('layouts.print')

@section('title', 'OPD Slip - ' . $consultation->patient->full_name)

@section('content')
    <style>
        /* Exact A4 Sheet Page Setup */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            color: #0f172a;
            background: transparent;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-container {
            padding: 0 !important;
            margin: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            box-sizing: border-box;
            position: relative;
        }

        /* Container leaving margin for preprinted header and left side filing holes */
        .opd-slip-wrapper {
            position: absolute;
            top: 5.2cm;            /* Vertical space below pre-printed hospital logo & header */
            left: 2.2cm;           /* Margin on left side for binder/file holes */
            right: 1.5cm;          /* Right side margin */
            box-sizing: border-box;
        }

        /* Main Details Box */
        .details-box {
            border: 1.5px solid #1e293b;
            border-radius: 10px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.85);
            box-sizing: border-box;
        }

        /* Header Row inside the Box */
        .box-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 6px;
            margin-bottom: 8px;
            border-bottom: 1px solid #cbd5e1;
        }

        .box-title {
            font-size: 11.5pt;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #0f172a;
        }

        .box-uhid-barcode {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .uhid-text {
            font-size: 10.5pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #0f172a;
        }

        .barcode-container {
            display: flex;
            align-items: center;
        }

        .barcode-container svg {
            height: 20px !important;
            max-width: 140px;
        }

        /* Two Column Layout */
        .columns-container {
            display: flex;
            justify-content: space-between;
            gap: 24px;
        }

        .details-column {
            flex: 1;
            min-width: 0;
        }

        /* Table based data alignment */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 2.5px 0;
            font-size: 10pt;
            vertical-align: top;
            line-height: 1.35;
        }

        .col-label {
            font-weight: 700;
            color: #334155;
            white-space: nowrap;
            width: 1%;
            padding-right: 4px;
        }

        .col-colon {
            font-weight: 700;
            color: #334155;
            width: 12px;
            text-align: center;
            padding: 0 4px;
            white-space: nowrap;
        }

        .col-val {
            font-weight: 600;
            color: #000;
            white-space: nowrap;
        }

        .col-val-wrap {
            font-weight: 600;
            color: #000;
            word-break: break-word;
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

    <div class="print-container">
        <div class="opd-slip-wrapper">
            <div class="details-box">
                {{-- Header Row: Left Title & Right UHID + Barcode --}}
                <div class="box-header-row">
                    <div class="box-title">
                        OUT PATIENT DETAILS
                    </div>
                    <div class="box-uhid-barcode">
                        <span class="uhid-text">UHID : {{ $consultation->patient->uhid }}</span>
                        <div class="barcode-container">
                            {!! \App\Helpers\BarcodeHelper::generate($consultation->patient->uhid, 'TYPE_CODE_128', 1.1, 20) !!}
                        </div>
                    </div>
                </div>

                {{-- Two Columns of Details --}}
                <div class="columns-container">
                    {{-- Left Column: Patient Info & Vitals --}}
                    <div class="details-column">
                        <table class="data-table">
                            <tr>
                                <td class="col-label">Patient Name</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">{{ trim($consultation->patient->first_name . ' ' . ($consultation->patient->last_name ?? '')) ?: 'NAME NOT FOUND' }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Age & Gender</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">{{ $consultation->patient->age ?: '--' }} / {{ $consultation->patient->gender ?: '--' }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Weight</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">{{ $consultation->weight ? $consultation->weight . ' kg' : '--' }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Temperature</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">{{ $consultation->temperature ? $consultation->temperature . ' °F' : '--' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Right Column: Appointment, Doctor & Payment Details --}}
                    <div class="details-column">
                        <table class="data-table">
                            <tr>
                                <td class="col-label">APP. Date & time</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">
                                    {{ $consultation->consultation_date ? $consultation->consultation_date->format('d/m/Y') : $consultation->created_at->format('d/m/Y') }}
                                    <span style="font-weight: 500; font-size: 9pt; color: #475569;">{{ $consultation->created_at->format('h:i A') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="col-label">Doctor Name</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">{{ $consultation->doctor ? $consultation->doctor->full_name : 'Dr. L. Avinash Rao' }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Valid Upto</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">{{ $consultation->valid_upto ? $consultation->valid_upto->format('d/m/Y') : '--' }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Paid</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">
                                    @if($consultation->visit_type === 'Review' || $consultation->fee <= 0)
                                        Review visit (₹0)
                                    @else
                                        ₹{{ number_format($consultation->fee, 0) }} ({{ strtoupper($consultation->payment_method ?? 'Cash') }})
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection