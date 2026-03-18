<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: localStorage.getItem('darkMode') === 'true',
          sidebarOpen: window.innerWidth >= 1024
      }"
      x-init="
          $watch('darkMode', val => localStorage.setItem('darkMode', val));
          window.addEventListener('resize', () => {
              if (window.innerWidth >= 1024) sidebarOpen = true;
          });
      "
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'HMS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        *, body { font-family: 'Inter', sans-serif; }
        -webkit-tap-highlight-color: transparent;
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased">

    <!-- ════════════════════════════
         MOBILE SIDEBAR OVERLAY
    ════════════════════════════ -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-cloak
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden">
    </div>

    <!-- ════════════════════════════
         APP SHELL
    ════════════════════════════ -->
    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR (slide-in on mobile, static on desktop) -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 flex flex-col transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:flex-shrink-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <x-sidebar />
        </aside>

        <!-- MAIN AREA -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <x-topbar />

            <main class="flex-1 overflow-y-auto">
                <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
