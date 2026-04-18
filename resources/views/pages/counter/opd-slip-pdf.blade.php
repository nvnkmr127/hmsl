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
        .growth-box { border-bottom: 1px dashed #333; padding-bottom: 10px; margin-top: 15px; }
        .visual-bar { height: 8px; background: #eee; border: 1px solid #333; position: relative; overflow: hidden; margin-top: 3px; }
    </style>
</head>
<body>
    <div class="header-spacing"></div>

    <table style="margin-bottom: 10px;">
        <tr>
            <td width="55%">
                <span class="label">Patient Name:</span><br>
                <span class="value" style="font-size: 14pt; letter-spacing: -0.05em;">{{ strtoupper($consultation->patient->full_name) }}</span>
                <span style="font-size: 8pt; color: #666; margin-left: 5px;">(UHID: {{ $consultation->patient->uhid }})</span>
            </td>
            <td width="45%" align="right">
                <span class="label">Token No:</span><br>
                <span class="token">#{{ $consultation->token_number }}</span>
            </td>
        </tr>
    </table>

    @php
        $growthService = app(\App\Services\GrowthChartService::class);
        $growthData = $growthService->getGrowthStatus($consultation->patient, $consultation->weight, $consultation->height);
    @endphp

    <div class="op-slip">
        <table>
            <tr>
                <td width="33%">
                    <span class="label">Consultation Date & Time</span><br>
                    <span class="value">{{ $consultation->consultation_date ? $consultation->consultation_date->format('d M, Y') : '--' }}</span>
                    <span style="font-size: 8pt; color: #666; margin-left: 3px;">{{ $consultation->created_at->format('h:i A') }}</span>
                </td>
                <td width="33%" align="center" style="border-left: 1px solid #eee; border-right: 1px solid #eee;">
                    <span class="label">Valid Upto</span><br>
                    <span class="value">{{ $consultation->valid_upto ? $consultation->valid_upto->format('d M, Y') : 'N/A' }}</span>
                </td>
                <td width="33%" align="right">
                    <span class="label">Consultation Fee</span><br>
                    <span class="value" style="font-size: 13pt;">₹{{ number_format($consultation->fee, 0) }}</span>
                    <span style="padding: 1px 3px; background: #333; color: #fff; font-size: 6pt; font-weight: bold; vertical-align: middle;">{{ strtoupper($consultation->payment_method) }}</span>
                </td>
            </tr>
            <tr><td colspan="3" style="height: 12px; border-bottom: 1px solid #eee; margin-bottom: 12px;"></td></tr>
            <tr><td colspan="3" style="height: 12px;"></td></tr>
            <tr>
                <td width="20%">
                    <span class="label">Age / Gender</span><br>
                    <span class="value">{{ $consultation->patient->age }} / {{ $consultation->patient->gender }}</span>
                </td>
                <td width="60%" align="center">
                    <table style="width: auto; margin: 0 auto;">
                        <tr>
                            <td style="padding: 0 10px; text-align: center;">
                                <span class="label">Weight</span><br>
                                <span class="value">{{ $consultation->weight ?? '--' }}kg</span>
                                @if($growthData && $growthData['weight']['expected_value'] != 'N/A')
                                    <div style="font-size: 7pt; color: #4338ca; font-weight: bold;">(Exp: {{ $growthData['weight']['expected_value'] }}kg)</div>
                                @endif
                            </td>
                            <td width="1" style="background: #eee;"></td>
                            <td style="padding: 0 10px; text-align: center;">
                                <span class="label">Height</span><br>
                                <span class="value">{{ $consultation->height ?? '--' }}cm</span>
                                @if($growthData && $growthData['height']['expected_value'] != 'N/A')
                                    <div style="font-size: 7pt; color: #4338ca; font-weight: bold;">(Exp: {{ $growthData['height']['expected_value'] }}cm)</div>
                                @endif
                            </td>
                            <td width="1" style="background: #eee;"></td>
                            <td style="padding: 0 10px; text-align: center;">
                                <span class="label">Temp</span><br>
                                <span class="value">{{ $consultation->temperature ?? '--' }}°F</span>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="20%" align="right" valign="top">
                    <span class="label">Status</span><br>
                    <span class="value" style="color: #059669; font-size: 9pt;">{{ strtoupper($consultation->payment_status) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 15px;">
        <span class="label">Consultant:</span><br>
        <span class="value" style="font-size: 12pt;">Dr. {{ $consultation->doctor->full_name ?? 'Resident Doctor' }}</span>
    </div>


    <div class="rx">Rx</div>

    <div class="footer">
        Generated on {{ now()->format('d/m/Y H:i') }} | Indian Pediatric Growth Standard Applied.
    </div>
</body>
</html>
