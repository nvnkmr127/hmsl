<div wire:poll.30s="pollStatus">
@if(!$isFullPage)
    <!-- Floating Widget Layout -->
    <div class="fixed bottom-6 right-6 z-[60]" x-data="{ expanded: false }" @mouseenter="expanded = true" @mouseleave="expanded = false">
        <div 
            class="flex items-center bg-white dark:bg-gray-900 shadow-[0_20px_50px_rgba(0,0,0,0.15)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-gray-800 transition-all duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] overflow-hidden"
            :class="expanded ? 'max-w-[400px] rounded-2xl p-3 pr-5' : 'max-w-[48px] h-[48px] rounded-full p-2'"
        >
            <!-- Icon / Indicator (Always centered in the 48px circle) -->
            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center relative mx-auto">
                <div @class([
                    'absolute inset-0 rounded-full blur-[8px] transition-all duration-500',
                    'bg-emerald-500/30' => $status === 'synced' && $pendingChanges === 0,
                    'bg-amber-500/30' => $pendingChanges > 0,
                    'bg-red-500/30' => $status === 'offline',
                    'bg-blue-500/30' => $isSyncing,
                    'animate-pulse' => $isSyncing
                ])></div>
                
                @if($isSyncing)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500 animate-spin relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                @else
                    <div @class([
                        'relative z-10 w-2.5 h-2.5 rounded-full transition-all duration-500',
                        'bg-emerald-500' => $status === 'synced' && $pendingChanges === 0,
                        'bg-amber-500 scale-110' => $pendingChanges > 0,
                        'bg-red-500' => $status === 'offline',
                        'bg-indigo-500' => $status === 'online',
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
                    {{ $isSyncing ? 'Synchronizing Data...' : ($status === 'offline' ? 'Offline' : ($pendingChanges > 0 ? "{$pendingChanges} Pending Changes" : 'Cloud Network Synced')) }}
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
@else
    <!-- Full Settings Page Layout -->
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm rounded-2xl p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Sync Status & Diagnostics</h2>
                <p class="text-xs text-gray-400 mt-1">Check connectivity with the central cloud server and trigger manual updates.</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <button 
                    wire:click="triggerSync"
                    wire:loading.attr="disabled"
                    @class([
                        'px-5 py-2.5 rounded-xl text-white font-bold text-sm transition-all duration-300 flex items-center space-x-2 active:scale-95 shadow-md',
                        'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/10' => !$isSyncing,
                        'bg-indigo-400 cursor-not-allowed shadow-none' => $isSyncing
                    ])
                >
                    <svg xmlns="http://www.w3.org/2000/svg" @class(['h-4 w-4', 'animate-spin' => $isSyncing]) fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{{ $isSyncing ? 'Syncing...' : 'Sync Now' }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <!-- Connection Status -->
            <div class="flex items-start space-x-4 p-4 rounded-xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800">
                <div @class([
                    'p-3 rounded-lg',
                    'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400' => $isOnline && $status !== 'error',
                    'bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400' => !$isOnline,
                    'bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' => $status === 'error'
                ])>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Connection Status</span>
                    <div class="flex items-center space-x-2 mt-1">
                        @if($isSyncing)
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase animate-pulse">Syncing...</span>
                        @elseif($status === 'offline')
                            <span class="text-sm font-bold text-red-600 dark:text-red-400 uppercase">Offline</span>
                        @elseif($status === 'error')
                            <span class="text-sm font-bold text-amber-600 dark:text-amber-400 uppercase">Sync Error</span>
                        @elseif($status === 'synced' && $pendingChanges === 0)
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase">Synced</span>
                        @else
                            <span class="text-sm font-bold text-amber-600 dark:text-amber-400 uppercase">Pending ({{ $pendingChanges }} changes)</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1 font-medium">{{ $isOnline ? 'Connected to remote sync node' : 'Offline Mode - Data will queue locally' }}</p>
                </div>
            </div>

            <!-- Last Pulsed / Synced -->
            <div class="flex items-start space-x-4 p-4 rounded-xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Last Sync Pulse</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $lastSyncAt }}</h3>
                    <p class="text-[11px] text-gray-400 mt-1 font-medium">{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->toDayDateTimeString() : 'Never synchronized' }}</p>
                </div>
            </div>

            <!-- Outbox Queue status -->
            <div class="flex items-start space-x-4 p-4 rounded-xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800">
                <div @class([
                    'p-3 rounded-lg',
                    'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400' => $pendingChanges === 0,
                    'bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' => $pendingChanges > 0
                ])>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Outbox Change Queue</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $pendingChanges }} records</h3>
                    <p class="text-[11px] text-gray-400 mt-1 font-medium">{{ $pendingChanges > 0 ? 'Changes queued to sync once online' : 'All local changes synchronized' }}</p>
                </div>
            </div>
        </div>

        <!-- Embedded Collapsible Conflicts Table -->
        @if($conflicts->isNotEmpty())
            <div class="mt-8 border border-red-200 dark:border-red-900 rounded-xl overflow-hidden bg-red-50/20 dark:bg-red-950/10" x-data="{ open: true }">
                <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-950/20 border-b border-red-200 dark:border-red-900 cursor-pointer" @click="open = !open">
                    <div class="flex items-center space-x-2 text-red-800 dark:text-red-300 font-bold uppercase text-xs tracking-wider">
                        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Unresolved Sync Conflicts ({{ $conflicts->count() }})</span>
                    </div>
                    <svg class="w-4 h-4 text-red-800 dark:text-red-300 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                
                <div class="p-4" x-show="open" x-collapse>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-red-200 dark:divide-red-900 text-sm">
                            <thead>
                                <tr class="text-left text-red-750 dark:text-red-400 uppercase text-[10px] tracking-wider font-bold">
                                    <th class="pb-2 font-black">Table</th>
                                    <th class="pb-2 font-black">Record UUID</th>
                                    <th class="pb-2 font-black">Local Version</th>
                                    <th class="pb-2 font-black">Server Version</th>
                                    <th class="pb-2 font-black text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-100 dark:divide-red-950">
                                @foreach($conflicts as $conflict)
                                    <tr class="align-top">
                                        <td class="py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $conflict->table_name }}</td>
                                        <td class="py-3 text-xs text-gray-500 font-mono">{{ $conflict->record_uuid }}</td>
                                        <td class="py-3">
                                            <pre class="text-[10px] bg-white dark:bg-gray-950 p-2.5 rounded-lg border border-red-100 dark:border-red-900/40 overflow-auto max-w-[220px] max-h-[120px] font-mono text-gray-700 dark:text-gray-300">{{ json_encode($conflict->local_data, JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                        <td class="py-3">
                                            <pre class="text-[10px] bg-white dark:bg-gray-950 p-2.5 rounded-lg border border-red-100 dark:border-red-900/40 overflow-auto max-w-[220px] max-h-[120px] font-mono text-gray-700 dark:text-gray-300">{{ json_encode($conflict->server_data, JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                        <td class="py-3 text-right space-y-2">
                                            <div class="flex flex-col space-y-1.5 justify-end items-end">
                                                <button wire:click="resolveConflict({{ $conflict->id }}, 'local')" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-black uppercase tracking-wider transition-all shadow-sm">Keep Local</button>
                                                <button wire:click="resolveConflict({{ $conflict->id }}, 'server')" class="px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 text-white text-xs font-black uppercase tracking-wider transition-all shadow-sm">Keep Server</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
</div>
