<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print - {{ $title ?? 'Document' }}</title>
    
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: 'Outfit', sans-serif; font-size: 12pt; line-height: 1.5; color: #111; margin: 0; padding: 0; background: #fff; }
        .print-container { width: 100%; max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #ccc; margin-bottom: 30px; }
        .hospital-info h1 { margin: 0; font-size: 24pt; font-weight: 700; color: #4F46E5; }
        .hospital-info p { margin: 2px 0; color: #555; }
        .footer { position: fixed; bottom: 0; width: 100%; border-top: 1px solid #eee; padding-top: 10px; font-size: 8pt; color: #888; text-align: center; }
        .content { margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th { background: #f8f8f8; text-align: left; padding: 10px; border-bottom: 2px solid #eee; }
        .table td { padding: 10px; border-bottom: 1px solid #eee; }
        .no-print { display: none; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        @yield('content')
        {{ $slot ?? '' }}
    </div>
</body>
</html>
