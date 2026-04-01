<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HMS') }} — Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex bg-[#f8f9fc] dark:bg-[#060c1a] antialiased">

    <!-- Left panel: branding -->
    <div class="hidden lg:flex lg:w-[44%] flex-col justify-between p-14" style="background:#0f172a">
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#7c3aed">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <span class="text-white font-bold text-base">{{ config('app.name', 'HMS') }}</span>
        </div>

        <!-- Center copy -->
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-8 text-xs font-semibold" style="background:rgba(124,58,237,0.15);color:#a78bfa">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                All systems operational
            </div>
            <h2 class="text-4xl font-extrabold text-white leading-tight mb-4" style="letter-spacing:-0.03em">
                Hospital Management<br><span style="color:#7c3aed">Intelligence Suite</span>
            </h2>
            <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                Unified platform for clinical operations, billing, stats, and patient lifecycle management.
            </p>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mt-10">
                <div class="p-4 rounded-xl" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
                    <p class="text-xl font-bold text-white">98%</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Uptime</p>
                </div>
                <div class="p-4 rounded-xl" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
                    <p class="text-xl font-bold text-white">24/7</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Support</p>
                </div>
                <div class="p-4 rounded-xl" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
                    <p class="text-xl font-bold text-white">SSL</p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Encrypted</p>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-600">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>

    <!-- Right panel: form -->
    <div class="flex-1 flex flex-col items-center justify-center px-8 py-12">
        <!-- Mobile logo -->
        <div class="lg:hidden flex items-center gap-3 mb-10">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#7c3aed">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <span class="font-bold text-slate-900 dark:text-white">{{ config('app.name') }}</span>
        </div>

        <div class="w-full max-w-sm">
            <!-- Dark mode toggle -->
            <div class="flex justify-end mb-8">
                <button @click="darkMode = !darkMode"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 transition-all">
                    <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
            </div>

            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-1" style="letter-spacing:-0.02em">Welcome back</h1>
            <p class="text-sm text-slate-500 mb-8">Sign in to your HMS account</p>

            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </div>
</body>
</html>
