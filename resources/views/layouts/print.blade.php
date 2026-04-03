<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print - {{ $title ?? 'Document' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Outfit', -apple-system, sans-serif; font-size: 11pt; line-height: 1.4; color: #1e293b; margin: 0; padding: 0; background: #fff; -webkit-print-color-adjust: exact; }
        .print-container { width: 210mm; min-height: 297mm; padding: 20mm; margin: 0 auto; box-sizing: border-box; position: relative; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 4px solid #f1f5f9; margin-bottom: 32px; }
        .hospital-identity { display: flex; align-items: center; gap: 16px; }
        .hospital-logo { width: 48px; h-48px; border-radius: 12px; background: #4f46e5; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 20pt; }
        .hospital-info h1 { margin: 0; font-size: 22pt; font-weight: 900; color: #0f172a; letter-spacing: -0.02em; }
        .hospital-info p { margin: 1px 0; color: #64748b; font-size: 9pt; font-weight: 500; }
        .footer { position: absolute; bottom: 20mm; left: 20mm; right: 20mm; border-top: 1px solid #f1f5f9; padding-top: 16px; font-size: 8pt; color: #94a3b8; text-align: center; }
        .content { margin-top: 0; }
        .table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        .table th { background: #f8fafc; text-align: left; padding: 12px 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 8pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; }
        .table td { padding: 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-black { font-weight: 900; }
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
