<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First Run Setup</title>
    <meta http-equiv="refresh" content="2">
    <link rel="stylesheet" href="/fonts/outfit.css">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-900 text-white flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden select-none">
    <div class="max-w-md w-full text-center space-y-8">
        <!-- Logo / Icon -->
        <div class="relative w-24 h-24 mx-auto flex items-center justify-center">
            <div class="absolute inset-0 rounded-full bg-indigo-500/20 blur-xl animate-pulse"></div>
            <div class="relative z-10 w-16 h-16 rounded-3xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-8 h-8 text-white animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>

        <div class="space-y-3">
            <h1 class="text-2xl font-extrabold uppercase tracking-wider text-indigo-400">Initializing System</h1>
            <p class="text-sm font-semibold text-gray-400">Setting up HMS for first use...</p>
        </div>

        <!-- Progress Indicator -->
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-2xl p-5 text-xs text-gray-400 space-y-2">
            <p class="font-medium animate-pulse">Running migrations & database seeders...</p>
            <p class="text-[10px] text-indigo-400/70 font-semibold tracking-widest uppercase">Do not close this window</p>
        </div>
    </div>
</body>
</html>
