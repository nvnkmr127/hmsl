<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; border-bottom: 2px solid #7c3aed; padding-bottom: 20px; margin-bottom: 20px; }
        .hospital-name { font-size: 24px; font-weight: 800; color: #7c3aed; text-transform: uppercase; }
        .prescription-title { font-size: 18px; font-weight: bold; color: #555; }
        .medicine-table { w-full; border-collapse: collapse; margin-top: 20px; }
        .medicine-table th { text-align: left; border-bottom: 1px solid #eee; padding: 10px; font-size: 12px; color: #888; text-transform: uppercase; }
        .medicine-table td { padding: 10px; border-bottom: 1px solid #f9f9f9; }
        .footer { margin-top: 30px; font-size: 12px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="hospital-name">{{ config('app.name') }}</div>
            <div class="prescription-title">Prescription Details</div>
        </div>

        <p>Dear <strong>{{ $prescription->patient->full_name }}</strong>,</p>
        <p>Please find the clinical prescription details from your recent consultation with <strong>Dr. {{ $prescription->doctor->full_name }}</strong>.</p>

        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 0; font-size: 14px;"><strong>Diagnosis:</strong> {{ $prescription->diagnosis ?: 'N/A' }}</p>
        </div>

        <table class="medicine-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prescription->medicines as $med)
                    <tr>
                        <td><strong>{{ $med['name'] }}</strong></td>
                        <td>{{ $med['dosage'] }}</td>
                        <td>{{ $med['duration'] }} - {{ $med['instructions'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($prescription->advice)
            <div style="margin-top: 20px;">
                <p><strong>Clinical Advice:</strong></p>
                <p>{{ $prescription->advice }}</p>
            </div>
        @endif

        <div class="footer">
            <p>This is an automated clinical document. For any queries, please visit the hospital contact desk.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
