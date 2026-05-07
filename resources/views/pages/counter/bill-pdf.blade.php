<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill — {{ $bill->bill_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #000; margin-bottom: 10px; }
        .hospital-name { font-size: 18pt; font-weight: bold; }
        .bill-title { font-size: 14pt; font-weight: bold; text-align: right; }
        
        .info-table { width: 100%; border: 1px solid #000; padding: 5px; margin-bottom: 15px; }
        .info-label { font-weight: bold; width: 120px; }
        .info-value { font-weight: normal; }

        .billing-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .billing-table th { background: #eee; border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold; }
        .billing-table td { border: 1px solid #ccc; padding: 5px; }
        
        .group-header { background: #f9f9f9; font-weight: bold; }
        .group-total { text-align: right; font-weight: bold; background: #fff; }

        .summary-table { width: 100%; margin-top: 10px; }
        .totals-table { width: 300px; float: right; border: 1px solid #000; border-collapse: collapse; }
        .totals-table td { padding: 5px; border: 1px solid #eee; }
        .grand-total { background: #eee; font-weight: bold; font-size: 11pt; }

        .footer-table { width: 100%; margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; }
        .barcode { font-family: monospace; font-size: 10pt; }
        .signature { text-align: center; width: 200px; border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        
        .clear { clear: both; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="hospital-name">{{ config('app.name', 'HMS') }}</td>
            <td class="bill-title">FINAL BILL</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label">Patient Name:</td>
            <td class="info-value">{{ strtoupper($bill->patient->full_name) }}</td>
            <td class="info-label">UHID:</td>
            <td class="info-value">{{ $bill->patient->uhid }}</td>
        </tr>
        <tr>
            <td class="info-label">Age / Gender:</td>
            <td class="info-value">{{ $bill->patient->age }} / {{ $bill->patient->gender }}</td>
            <td class="info-label">Bill No:</td>
            <td class="info-value">{{ $bill->bill_number }}</td>
        </tr>
        <tr>
            <td class="info-label">Doctor:</td>
            <td class="info-value">DR. {{ strtoupper($bill->admission?->doctor?->full_name ?? $bill->consultation?->doctor?->full_name ?? 'MEDICAL TEAM') }}</td>
            <td class="info-label">Date:</td>
            <td class="info-value">{{ $bill->created_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="info-label">Address:</td>
            <td colspan="3" class="info-value">{{ $bill->patient->address }}, {{ $bill->patient->city }}</td>
        </tr>
    </table>

    <table class="billing-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 60px; text-align: center;">Qty</th>
                <th style="width: 80px; text-align: right;">Rate</th>
                <th style="width: 80px; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = $bill->items->groupBy('item_type');
            @endphp
            @foreach($groupedItems as $type => $items)
                <tr class="group-header">
                    <td colspan="4">{{ strtoupper($type ?: 'SERVICES') }}</td>
                </tr>
                @foreach($items as $item)
                    <tr>
                        <td>{{ strtoupper($item->item_name) }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="group-total">{{ strtoupper($type) }} TOTAL:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($items->sum('total_price'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-weight: bold; margin-bottom: 20px;">
        IN WORDS: RUPEES {{ strtoupper($bill->amount_in_words) }} ONLY
    </div>

    <div class="summary-area">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: top;">
                    <table class="billing-table" style="width: 300px;">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th style="text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->payments as $payment)
                                <tr>
                                    <td>{{ strtoupper($payment->method) }}</td>
                                    <td style="text-align: right;">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td style="vertical-align: top;">
                    <table class="totals-table">
                        <tr>
                            <td>Gross Amount</td>
                            <td style="text-align: right;">{{ number_format($bill->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td style="text-align: right;">{{ number_format($bill->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Net Bill</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Paid Amount</td>
                            <td style="text-align: right;">{{ number_format($bill->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>Balance Payable</td>
                            <td style="text-align: right;">{{ number_format($bill->balance_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>

    <table class="footer-table">
        <tr>
            <td>
                <div class="barcode">UHID: {{ $bill->patient->uhid }}</div>
                <div class="barcode">BILL: {{ $bill->bill_number }}</div>
            </td>
            <td style="text-align: right;">
                <div style="display: inline-block; text-align: center;">
                    <div style="margin-bottom: 40px;">For {{ config('app.name') }}</div>
                    <div style="border-top: 1px solid #000; width: 150px;">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
