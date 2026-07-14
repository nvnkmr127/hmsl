<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill — {{ $bill->bill_number }}</title>
    <style>
        @page { size: a5 portrait; margin: 10mm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 8pt; 
            color: #1e293b; 
            line-height: 1.3; 
            margin: 0; 
            padding: 0; 
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .hospital-name { 
            font-size: 14pt; 
            font-weight: bold; 
            text-transform: uppercase;
            color: #0f172a;
        }
        .bill-title { 
            font-size: 12pt; 
            font-weight: bold; 
            text-align: right; 
        }
        
        .patient-table { 
            width: 100%; 
            margin-bottom: 15px; 
        }
        .info-label { 
            font-weight: bold; 
            color: #64748b;
            width: 80px;
            font-size: 7pt;
            text-transform: uppercase;
        }
        .info-val { 
            font-weight: bold; 
            color: #000;
            text-transform: uppercase;
        }

        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        .items-table th { 
            background: #0f172a; 
            color: #fff;
            padding: 6px 8px; 
            text-align: left; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .items-table td { 
            border-bottom: 1px solid #f1f5f9; 
            padding: 6px 8px; 
        }
        .group-header { 
            background: #f8fafc; 
            font-weight: bold; 
            color: #6366f1;
        }

        .footer-row {
            margin-top: 10px;
        }
        .payments-table {
            width: 55%;
            float: left;
        }
        .totals-table { 
            width: 40%; 
            float: right; 
        }
        .total-line {
            padding: 3px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-line.grand {
            border-top: 2px solid #0f172a;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 5px;
            padding-top: 5px;
        }

        .amount-words {
            background: #f8fafc;
            padding: 6px;
            font-style: italic;
            margin: 10px 0;
            font-size: 7pt;
        }

        .signature-area {
            margin-top: 30px;
        }
        .sig-box {
            float: right;
            text-align: center;
            width: 150px;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 30px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="hospital-name">{{ \App\Models\Setting::get('hospital_name', config('app.name')) }}</div>
                    <div style="font-size: 7pt; color: #64748b;">
                        {{ \App\Models\Setting::get('hospital_address') }}, {{ \App\Models\Setting::get('hospital_city') }}<br>
                        Ph: {{ \App\Models\Setting::get('hospital_phone') }} | Email: {{ \App\Models\Setting::get('hospital_email') }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div class="bill-title">{{ $bill->admission_id ? 'FINAL BILL' : 'OP BILL' }}</div>
                    <div style="font-size: 9pt; font-weight: bold; color: #6366f1;">#{{ $bill->bill_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="patient-table">
        <tr>
            <td class="info-label">Patient:</td>
            <td class="info-val">{{ $bill->patient->full_name }}</td>
            <td class="info-label">UHID:</td>
            <td class="info-val">{{ $bill->patient->uhid }}</td>
        </tr>
        <tr>
            <td class="info-label">Age / Gender:</td>
            <td class="info-val">{{ $bill->patient->age }} / {{ $bill->patient->gender }}</td>
            <td class="info-label">Bill Date:</td>
            <td class="info-val">
                @if(!$bill->admission && $bill->consultation)
                    {{ $bill->consultation->consultation_date->format('d/m/Y') }} {{ $bill->created_at->format('h:i A') }}
                @else
                    {{ $bill->created_at->format('d/m/Y h:i A') }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Doctor:</td>
            <td class="info-val">{{ strtoupper($bill->admission?->doctor?->full_name ?? $bill->consultation?->doctor?->full_name ?? 'MEDICAL TEAM') }}</td>
            @if($bill->admission)
                <td class="info-label">IP Number:</td>
                <td class="info-val">{{ $bill->admission->admission_number }}</td>
            @else
                <td class="info-label">Visit Type:</td>
                <td class="info-val">{{ strtoupper($bill->consultation?->visit_type ?? 'OPD') }}</td>
            @endif
        </tr>
        <tr>
            <td class="info-label">Contact:</td>
            <td class="info-val">{{ $bill->patient->phone ?? '—' }}</td>
            @if($bill->admission)
                <td class="info-label">Ward / Bed:</td>
                <td class="info-val">{{ strtoupper($bill->admission->ward_name) }}</td>
            @else
                <td class="info-label"></td>
                <td class="info-val"></td>
            @endif
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 30px; text-align: center;">Qty</th>
                <th style="width: 70px; text-align: right;">Rate</th>
                <th style="width: 70px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = $bill->items->groupBy('item_type');
            @endphp
            @foreach($groupedItems as $type => $items)
                <tr class="group-header">
                    <td colspan="4" style="font-size: 7pt; padding: 4px 8px;">{{ strtoupper($type ?: 'SERVICES') }}</td>
                </tr>
                @foreach($items as $item)
                    <tr>
                        <td style="font-weight: 500;">{{ str_replace('DR. DR.', 'DR.', strtoupper($item->item_name)) }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="amount-words">
        RUPEES {{ strtoupper($bill->amount_in_words) }} ONLY
    </div>

    <div class="footer-row">
        <div class="totals-table" style="width: 180px; float: right;">
            <div class="total-line">Subtotal <span style="float: right;">₹{{ number_format($bill->subtotal, 2) }}</span><div class="clear"></div></div>
            <div class="total-line">Discount <span style="float: right;">- ₹{{ number_format($bill->discount_amount, 2) }}</span><div class="clear"></div></div>
            <div class="total-line">Paid <span style="float: right;">₹{{ number_format($bill->paid_amount, 2) }}</span><div class="clear"></div></div>
            <div class="total-line grand">Balance <span style="float: right;">₹{{ number_format($bill->balance_amount, 2) }}</span><div class="clear"></div></div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="signature-area">
        <div style="float: left; font-size: 6pt; color: #94a3b8; margin-top: 40px;">
            Generated by: {{ strtoupper($bill->creator?->name ?? 'Admin') }}
        </div>
        <div class="sig-box">
            <div class="sig-line">Authorized Signatory</div>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
