<div class="fixed bottom-6 right-6 z-[60]" x-data="{ expanded: false }" @mouseenter="expanded = true" @mouseleave="expanded = false">
    <div 
        class="flex items-center bg-white dark:bg-gray-900 shadow-[0_20px_50px_rgba(0,0,0,0.15)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-gray-800 transition-all duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] overflow-hidden"
        :class="expanded ? 'max-w-[400px] rounded-2xl p-3 pr-5' : 'max-w-[48px] h-[48px] rounded-full p-2'"
    >
        <!-- Icon / Indicator (Always centered in the 48px circle) -->
        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center relative mx-auto">
            <div @class([
                'absolute inset-0 rounded-full blur-[8px] transition-all duration-500',
                'bg-emerald-500/30' => $pendingChanges === 0,
                'bg-amber-500/30' => $pendingChanges > 0,
                'animate-pulse' => $isSyncing
            ])></div>
            
            @if($isSyncing)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500 animate-spin relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            @else
                <div @class([
                    'relative z-10 w-2.5 h-2.5 rounded-full transition-all duration-500',
                    'bg-emerald-500' => $pendingChanges === 0,
                    'bg-amber-500 scale-110' => $pendingChanges > 0,
                ])></div>
            @endif
        </div>

        <!-- Expanded Content (Shown only on hover) -->
        <div 
            class="ml-3 flex flex-col transition-all duration-500 whitespace-nowrap"
            x-show="expanded"
            x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 -translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
        >
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Sync Hub</span>
                @if($isSyncing)
                    <span class="flex h-1 w-1 rounded-full bg-indigo-500 animate-ping"></span>
                @endif
            </div>
            <p class="text-[11px] font-black text-gray-900 dark:text-white uppercase tracking-tight">
                {{ $isSyncing ? 'Synchronizing Data...' : ($pendingChanges > 0 ? "{$pendingChanges} Pending Changes" : 'Cloud Network Synced') }}
            </p>
            <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5 opacity-60">Last Pulse: {{ $lastSyncAt }}</p>
        </div>

        <!-- Sync Action Button -->
        <button 
            wire:click="triggerSync"
            wire:loading.attr="disabled"
            x-show="expanded"
            x-transition:enter="transition ease-out duration-300 delay-200"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="ml-6 p-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all duration-300 active:scale-90"
            title="Force Sync Now"
        >
             <svg xmlns="http://www.w3.org/2000/svg" @class(['h-4 w-4', 'animate-spin' => $isSyncing]) fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>
</div>
