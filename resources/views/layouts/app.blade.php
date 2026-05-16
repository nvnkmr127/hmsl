<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: localStorage.getItem('darkMode') === 'true',
          sidebarOpen: window.innerWidth >= 1024,
          resizeTicking: false,
          handleResize() {
              if (this.resizeTicking) return;
              this.resizeTicking = true;
              requestAnimationFrame(() => {
                  if (window.innerWidth >= 1024) this.sidebarOpen = true;
                  this.resizeTicking = false;
              });
          },
          isMac: /Mac|iPod|iPhone|iPad/.test(navigator.platform || '')
      }"
      x-init="
          $watch('darkMode', val => localStorage.setItem('darkMode', val));
          window.addEventListener('resize', () => handleResize());
      "
      x-on:keydown.alt.n.window="$dispatch('quick-op-booking')"
      x-on:keydown.alt.s.window="$dispatch('open-global-search')"
      x-on:keydown.meta.alt.n.window="$dispatch('quick-op-booking')"
      x-on:keydown.meta.alt.s.window="$dispatch('open-global-search')"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'HMS') }}</title>

    <link rel="stylesheet" href="/fonts/inter.css">

    <script src="/js/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        *, body { font-family: 'Inter', sans-serif; }
        html, body { -webkit-tap-highlight-color: transparent; }
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

    <livewire:counter.quick-op-booking />
    <livewire:counter.patient-form />
    <x-toast />

    @livewireScripts
    <script>
        let lastPrintUrl = null;
        let lastPrintTime = 0;

        function printUrl(url) {
            const now = Date.now();
            if (url === lastPrintUrl && (now - lastPrintTime) < 2000) {
                console.log('Duplicate print request blocked');
                return;
            }
            lastPrintUrl = url;
            lastPrintTime = now;

            console.log('Printing URL:', url);
            
            const existing = document.getElementById('print-iframe');
            if (existing) existing.remove();

            const iframe = document.createElement('iframe');
            iframe.id = 'print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '100%';
            iframe.style.bottom = '100%';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            iframe.src = url;

            document.body.appendChild(iframe);

            iframe.onload = function() {
                setTimeout(() => {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }, 300);
            };
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('print-op-slip', (event) => {
                const id = event.id ?? event[0].id ?? event[0];
                if (!id) return;
                const url = "{{ route('counter.opd.print', ['id' => ':id']) }}".replace(':id', id);
                printUrl(url);
            });

            Livewire.on('bill-generated', (event) => {
                const billId = event?.billId ?? event?.[0]?.billId ?? event?.[0]?.bill_id ?? event?.[0];
                if (!billId) return;
                const url = "{{ route('billing.bills.print', ['bill' => ':id']) }}".replace(':id', billId);
                printUrl(url);
            });

            Livewire.on('prescription-generated', (event) => {
                const id = event.id ?? event[0].id ?? event[0];
                if (!id) return;
                const url = "{{ route('counter.prescriptions.print', ['id' => ':id']) }}".replace(':id', id);
                printUrl(url);
            });

            Livewire.on('booking-completed', () => {
                const searchInput = document.getElementById('patient-search-input');
                if (searchInput) {
                    setTimeout(() => {
                        searchInput.focus();
                        searchInput.select();
                    }, 300);
                }
            });

            // Background Sync Logic
            window.addEventListener('online', () => {
                console.log('System is online. Triggering sync...');
                Livewire.dispatch('trigger-background-sync');
            });

            // Heartbeat sync every 5 minutes if online
            setInterval(() => {
                if (navigator.onLine) {
                    console.log('Heartbeat: Triggering background sync...');
                    Livewire.dispatch('trigger-background-sync');
                }
            }, 5 * 60 * 1000);
        });

        // Global Interceptor for all "Print" related links and buttons
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[target="_blank"]');
            if (link && (link.href.includes('print') || link.innerText.toLowerCase().includes('print'))) {
                e.preventDefault();
                printUrl(link.href);
            }
        }, true);
    </script>
    @livewire('sync.sync-status')
    @stack('scripts')
</body>
</html>
