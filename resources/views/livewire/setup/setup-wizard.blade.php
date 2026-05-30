<div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden">

    {{-- Header --}}
    <div class="px-8 pt-8 pb-6 border-b border-slate-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-white font-semibold text-lg leading-tight">HMS Desktop</h1>
                <p class="text-slate-400 text-xs">First-run Setup</p>
            </div>
        </div>

        {{-- Step Indicator --}}
        <div class="flex items-center gap-2">
            @foreach([1 => 'Server', 2 => 'Device', 3 => 'Sync', 4 => 'Ready'] as $n => $label)
                <div class="flex items-center {{ $n < 4 ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center">
                        <div @class([
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all',
                            'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' => $step === $n,
                            'bg-emerald-600/80 text-white' => $step > $n,
                            'bg-slate-700 text-slate-400' => $step < $n,
                        ])>
                            @if($step > $n)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $n }}
                            @endif
                        </div>
                        <span @class([
                            'text-xs mt-1 font-medium',
                            'text-emerald-400' => $step === $n,
                            'text-emerald-500/70' => $step > $n,
                            'text-slate-500' => $step < $n,
                        ])>{{ $label }}</span>
                    </div>
                    @if($n < 4)
                        <div @class([
                            'flex-1 h-px mx-2 mb-5 transition-all',
                            'bg-emerald-600/60' => $step > $n,
                            'bg-slate-700' => $step <= $n,
                        ])></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Body --}}
    <div class="px-8 py-7">

        {{-- ─── Step 1: Connect to Server ─── --}}
        @if($step === 1)
            <h2 class="text-white text-xl font-semibold mb-1">Connect to Server</h2>
            <p class="text-slate-400 text-sm mb-6">Enter the URL of the online HMS server this machine will sync with.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-1.5">Server URL</label>
                    <input
                        wire:model="serverUrl"
                        type="url"
                        placeholder="https://your-hospital-server.com"
                        class="w-full bg-slate-900 border border-slate-600 text-white rounded-lg px-4 py-2.5 text-sm
                               placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                               transition"
                    />
                </div>

                @if($error)
                    <div class="flex items-start gap-2 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">
                        <svg class="w-4 h-4 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-red-400 text-sm">{{ $error }}</span>
                    </div>
                @endif

                @if($connectionOk)
                    <div class="flex items-center gap-2 text-emerald-400 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Connected successfully!
                    </div>
                @endif

                <button
                    wire:click="testConnection"
                    wire:loading.attr="disabled"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed
                           text-white font-semibold rounded-lg px-4 py-2.5 text-sm transition-colors flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                    <span wire:loading wire:target="testConnection" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Connecting…
                    </span>
                </button>
            </div>
        @endif

        {{-- ─── Step 2: Register Device ─── --}}
        @if($step === 2)
            <h2 class="text-white text-xl font-semibold mb-1">Register This Device</h2>
            <p class="text-slate-400 text-sm mb-6">Give this installation a name so you can identify it on the server dashboard.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-1.5">Device Name</label>
                    <input
                        wire:model="deviceName"
                        type="text"
                        placeholder="Reception PC 1"
                        class="w-full bg-slate-900 border border-slate-600 text-white rounded-lg px-4 py-2.5 text-sm
                               placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                               transition"
                    />
                    <p class="text-slate-500 text-xs mt-1.5">Device ID: {{ $deviceUuid }}</p>
                </div>

                @if($error)
                    <div class="flex items-start gap-2 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">
                        <svg class="w-4 h-4 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-red-400 text-sm">{{ $error }}</span>
                    </div>
                @endif

                <button
                    wire:click="registerDevice"
                    wire:loading.attr="disabled"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed
                           text-white font-semibold rounded-lg px-4 py-2.5 text-sm transition-colors flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove wire:target="registerDevice">Register &amp; Continue</span>
                    <span wire:loading wire:target="registerDevice" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Registering…
                    </span>
                </button>
            </div>
        @endif

        {{-- ─── Step 3: First Sync ─── --}}
        @if($step === 3)
            <h2 class="text-white text-xl font-semibold mb-1">Sync Data</h2>
            <p class="text-slate-400 text-sm mb-6">Pull all patients, appointments, and the latest data from the server.</p>

            <div class="space-y-5">
                @if(!$syncing && !$syncDone)
                    <div class="flex flex-col items-center gap-4 py-4">
                        <div class="w-16 h-16 rounded-full bg-slate-700 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <p class="text-slate-400 text-sm text-center">Ready to sync. This may take a minute on first run.</p>
                        <button
                            wire:click="runFirstSync"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg px-6 py-2.5 text-sm transition-colors"
                        >
                            Start Sync
                        </button>
                    </div>
                @endif

                @if($syncing)
                    <div class="flex flex-col items-center gap-4 py-4">
                        <svg class="animate-spin w-12 h-12 text-emerald-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <p class="text-slate-300 text-sm font-medium">Syncing data from server…</p>
                        <p class="text-slate-500 text-xs">Please keep this window open.</p>
                    </div>
                @endif

                @if($syncDone)
                    <div class="flex flex-col items-center gap-4 py-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-emerald-400 text-sm font-semibold">Sync complete!</p>
                        <button
                            wire:click="$set('step', 4)"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg px-6 py-2.5 text-sm transition-colors"
                        >
                            Continue
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- ─── Step 4: Ready ─── --}}
        @if($step === 4)
            <h2 class="text-white text-xl font-semibold mb-1">HMS Desktop is Ready</h2>
            <p class="text-slate-400 text-sm mb-6">Setup complete. The app works offline and syncs automatically when online.</p>

            <div class="space-y-3 mb-7">
                <div class="flex items-center gap-3 bg-slate-900/60 border border-slate-700 rounded-lg px-4 py-3">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div class="min-w-0">
                        <p class="text-slate-400 text-xs mb-0.5">Connected to</p>
                        <p class="text-white text-sm font-medium truncate">{{ $serverUrl }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-900/60 border border-slate-700 rounded-lg px-4 py-3">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Device</p>
                        <p class="text-white text-sm font-medium">{{ $deviceName }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-900/60 border border-slate-700 rounded-lg px-4 py-3">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Initial data</p>
                        <p class="text-white text-sm font-medium">Synced successfully</p>
                    </div>
                </div>
            </div>

            <a
                href="{{ url('/') }}"
                class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-500
                       text-white font-semibold rounded-lg px-4 py-3 text-sm transition-colors"
            >
                Open HMS
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        @endif

    </div>
</div>
