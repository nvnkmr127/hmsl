<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 20px; }
        .hospital-name { font-size: 24px; font-weight: 800; color: #10b981; text-transform: uppercase; }
        .invoice-title { font-size: 18px; font-weight: bold; color: #555; }
        .summary-table { w-full; border-collapse: collapse; margin-top: 20px; }
        .summary-table th { text-align: left; border-bottom: 1px solid #eee; padding: 10px; font-size: 12px; color: #888; text-transform: uppercase; }
        .summary-table td { padding: 10px; border-bottom: 1px solid #f9f9f9; }
        .total-row { font-size: 18px; font-weight: 900; color: #000; background: #f0fdf4; }
        .footer { margin-top: 30px; font-size: 12px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="hospital-name">{{ config('app.name') }}</div>
            <div class="invoice-title">Payment Receipt #{{ $bill->bill_number }}</div>
        </div>

        <p>Dear <strong>{{ $bill->patient->full_name }}</strong>,</p>
        <p>Thank you for choosing <strong>{{ config('app.name') }}</strong>. We have received your payment for the recent services.</p>

        <table class="summary-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->items as $item)
                    <tr>
                        <td>{{ $item->item_name }} @if($item->quantity > 1) (x{{ $item->quantity }}) @endif</td>
                        <td style="text-align: right;">₹{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                @if($bill->discount_amount > 0)
                    <tr>
                        <td style="text-align: right;">Discount</td>
                        <td style="text-align: right; color: #ef4444;">-₹{{ number_format($bill->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td style="text-align: right;">Total Paid</td>
                    <td style="text-align: right;">₹{{ number_format($bill->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: center;">
            <p style="font-size: 12px; color: #555;">Payment Method: <strong>{{ strtoupper($bill->payment_method) }}</strong> | Status: <strong>{{ strtoupper($bill->payment_status) }}</strong></p>
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt. No signature is required.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
