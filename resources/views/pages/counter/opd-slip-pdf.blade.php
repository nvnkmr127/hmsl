<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>OPD Slip - {{ $consultation->patient->full_name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #333; }
        .header-spacing { height: 5cm; } /* Adjust as needed */
        .op-slip { border-top: 1px dashed #000; border-bottom: 2px solid #000; padding: 10px 0; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .label { font-size: 8pt; color: #666; text-transform: uppercase; }
        .value { font-weight: bold; font-size: 11pt; }
        .token { font-size: 24pt; font-weight: 900; }
        .rx { margin-top: 50px; font-size: 40pt; color: #eee; font-style: italic; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8pt; text-align: center; color: #999; }
    </style>
</head>
<body>
    <div class="header-spacing"></div>

    <table style="margin-bottom: 10px;">
        <tr>
            <td width="70%">
                <span class="label">Patient Name:</span><br>
                <span class="value" style="font-size: 14pt;">{{ $consultation->patient->full_name }}</span>
            </td>
            <td width="30%" align="right">
                <span class="label">Token No:</span><br>
                <span class="token">#{{ $consultation->token_number }}</span>
            </td>
        </tr>
    </table>

    <div class="op-slip">
        <table>
            <tr>
                <td width="33%">
                    <span class="label">UHID:</span><br>
                    <span class="value">{{ $consultation->patient->uhid }}</span>
                </td>
                <td width="33%" align="center">
                    <span class="label">Date:</span><br>
                    <span class="value">{{ $consultation->consultation_date->format('d M, Y') }}</span>
                </td>
                <td width="33%" align="right">
                    <span class="label">Valid Upto:</span><br>
                    <span class="value">{{ $consultation->valid_upto?->format('d M, Y') ?? 'N/A' }}</span>
                </td>
            </tr>
            <tr><td colspan="3" style="height: 10px;"></td></tr>
            <tr>
                <td>
                    <span class="label">Age / Gender:</span><br>
                    <span class="value">{{ $consultation->patient->age }} / {{ $consultation->patient->gender }}</span>
                </td>
                <td align="center">
                    <span class="label">Vitals (Wt/Temp):</span><br>
                    <span class="value">{{ $consultation->weight ?? '--' }}kg / {{ $consultation->temperature ?? '--' }}°F</span>
                </td>
                <td align="right">
                    <span class="label">Fee / Status:</span><br>
                    <span class="value">₹{{ number_format($consultation->fee, 0) }} ({{ strtoupper($consultation->payment_method ?? 'Cash') }})</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <span class="label">Consultant:</span><br>
        <span class="value" style="font-size: 12pt;">Dr. {{ $consultation->doctor->full_name ?? 'Resident Doctor' }}</span>
        @if($consultation->doctor?->specialization)
            <br><span style="font-size: 9pt; color: #666;">{{ $consultation->doctor->specialization }}</span>
        @endif
    </div>

    <div class="rx">Rx</div>

    <div class="footer">
        Generated on {{ now()->format('d/m/Y H:i') }} | This is a computer generated slip.
    </div>
</body>
</html>
