<!DOCTYPE html>
<html lang="te">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print - {{ $title ?? 'Document' }}</title>

    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Noto Sans Telugu', -apple-system, sans-serif; font-size: 11pt; line-height: 1.55; color: #111827; margin: 0; padding: 0; background: #fff; -webkit-print-color-adjust: exact; }
        .print-container { width: 210mm; min-height: 297mm; padding: 18mm 16mm; margin: 0 auto; box-sizing: border-box; position: relative; }
        .no-print { display: none; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        @yield('content')
        {{ $slot ?? '' }}
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>

