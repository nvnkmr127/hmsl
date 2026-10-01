<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>OPD Slip - {{ $consultation->patient->full_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        .opd-slip-wrapper {
            margin-top: 5.2cm;
            margin-left: 2.2cm;
            margin-right: 1.5cm;
        }

        .details-box {
            border: 1.5px solid #1e293b;
            border-radius: 10px;
            padding: 10px 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #cbd5e1;
            margin-bottom: 8px;
            padding-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
            padding-bottom: 4px;
        }

        .box-title {
            font-size: 11.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #0f172a;
        }

        .uhid-text {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }

        .barcode-container {
            display: inline-block;
            vertical-align: middle;
        }

        table.columns-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.columns-table td {
            vertical-align: top;
            padding: 0;
        }

        table.inner-data {
            width: 100%;
            border-collapse: collapse;
        }

        table.inner-data td {
            padding: 2.5px 0;
            font-size: 9.5pt;
            vertical-align: top;
        }

        .col-label {
            font-weight: bold;
            color: #334155;
            white-space: nowrap;
            width: 1%;
            padding-right: 4px;
        }

        .col-colon {
            font-weight: bold;
            color: #334155;
            width: 12px;
            text-align: center;
            padding: 0 4px;
            white-space: nowrap;
        }

        .col-val {
            font-weight: bold;
            color: #000;
            white-space: nowrap;
        }

        .col-val-wrap {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="opd-slip-wrapper">
        <div class="details-box">
            {{-- Header Table: Left Title & Right UHID + Barcode --}}
            <table class="header-table">
                <tr>
                    <td align="left">
                        <div class="box-title">OUT PATIENT DETAILS</div>
                    </td>
                    <td align="right">
                        <span class="uhid-text">UHID : {{ $consultation->patient->uhid }}</span>
                        <div class="barcode-container">
                            {!! \App\Helpers\BarcodeHelper::generateHtml($consultation->patient->uhid, 'TYPE_CODE_128', 1.1, 18) !!}
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Two Columns of Details --}}
            <table class="columns-table">
                <tr>
                    {{-- Left Column: Patient Info & Vitals --}}
                    <td width="48%">
                        <table class="inner-data">
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
                    </td>

                    <td width="4%"></td>

                    {{-- Right Column: Appointment, Doctor & Payment Details --}}
                    <td width="48%">
                        <table class="inner-data">
                            <tr>
                                <td class="col-label">APP. Date & time</td>
                                <td class="col-colon">:</td>
                                <td class="col-val">
                                    {{ $consultation->consultation_date ? $consultation->consultation_date->format('d/m/Y') : $consultation->created_at->format('d/m/Y') }}
                                    <span style="font-weight: normal; font-size: 8.5pt; color: #475569;">{{ $consultation->created_at->format('h:i A') }}</span>
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
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
