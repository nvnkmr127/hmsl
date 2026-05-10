<div class="space-y-8 pb-12">
    <!-- Health Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-[2rem] shadow-xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 relative z-10">Queue Status</div>
            <div class="text-3xl font-black {{ $stats['pending_outbox'] > 10 ? 'text-amber-400' : 'text-emerald-400' }} relative z-10">
                {{ $stats['pending_outbox'] }} <span class="text-sm text-slate-500 font-bold">pending</span>
            </div>
            <!-- Mini Sparkline -->
            <div class="absolute bottom-0 left-0 right-0 h-1.5 flex gap-[1px] opacity-80">
                @foreach(range(0, 23) as $h)
                    @php 
                        $hourData = $stats['trend'][$h] ?? collect();
                        $hasFailures = $hourData->where('status', 'failed')->count() > 0;
                    @endphp
                    <div class="flex-1 h-full {{ $hourData->count() > 0 ? ($hasFailures ? 'bg-rose-500' : 'bg-emerald-500') : 'bg-slate-800' }}"></div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Active Endpoints</div>
            <div class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['active'] }}<span class="text-sm text-slate-400 font-bold">/{{ $stats['total'] }}</span></div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Success Rate</div>
            <div class="text-3xl font-black {{ $stats['success_rate'] > 95 ? 'text-indigo-500' : 'text-orange-500' }}">{{ $stats['success_rate'] }}%</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Avg Latency</div>
            <div class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['avg_latency'] }}<span class="text-sm text-slate-400 font-bold">ms</span></div>
        </div>

        <div class="bg-gradient-to-tr from-emerald-500/10 to-teal-400/5 dark:from-emerald-900/40 dark:to-teal-800/10 p-6 rounded-[2rem] border border-emerald-100 dark:border-emerald-800/30 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">Reliability</div>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400">99.9%</div>
            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-transform duration-500">
                <svg class="w-24 h-24 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/></svg>
            </div>
        </div>
    </div>

    <!-- Tabs Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
        <div class="inline-flex p-1.5 space-x-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700/50 shadow-inner">
            <button wire:click="$set('activeTab', 'outbound')" 
                    class="px-6 py-2.5 text-[11px] font-black uppercase tracking-[0.15em] rounded-xl transition-all duration-200 {{ $activeTab === 'outbound' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                Outgoing Webhooks
            </button>
            <button wire:click="$set('activeTab', 'inbound')" 
                    class="px-6 py-2.5 text-[11px] font-black uppercase tracking-[0.15em] rounded-xl transition-all duration-200 {{ $activeTab === 'inbound' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50' }}">
                Incoming Sources
            </button>
        </div>
        <button wire:click="openModal(null, '{{ $activeTab }}')" class="px-6 py-3 bg-slate-900 dark:bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.15em] hover:scale-105 hover:shadow-xl hover:shadow-indigo-500/20 active:scale-95 transition-all flex items-center space-x-2 border border-transparent dark:border-indigo-500/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>{{ $activeTab === 'outbound' ? 'Add Endpoint' : 'Add Source' }}</span>
        </button>
    </div>

    <!-- Outbound Content (Table Format) -->
    @if($activeTab === 'outbound')
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        @if($endpoints->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Endpoint Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest max-w-[300px]">Subscribed Events</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($endpoints as $ep)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                        <td class="px-6 py-5 align-top w-1/3">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 shrink-0">
                                    <div class="w-8 h-8 rounded-xl {{ $ep->is_active ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 border border-indigo-100 dark:border-indigo-800/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }} flex items-center justify-center shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors cursor-pointer" wire:click="openModal({{ $ep->id }}, 'outbound')">{{ $ep->name }}</h3>
                                    <p class="text-[10px] text-slate-500 font-mono font-bold truncate mt-1">{{ $ep->url }}</p>
                                    @if($ep->consecutive_failures > 0)
                                        <div class="mt-2 inline-flex items-center gap-1.5 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 px-2 py-1 rounded-md border border-rose-100 dark:border-rose-800/30">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span class="text-[9px] font-black uppercase tracking-widest">{{ $ep->consecutive_failures }} Failures</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top whitespace-nowrap">
                            @if($ep->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Suspended
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top">
                            <div class="flex flex-wrap gap-1.5 max-w-sm">
                                @foreach($ep->events as $index => $event)
                                    @if($index < 3)
                                        <span class="text-[9px] font-black bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 px-2 py-1 rounded border border-slate-200/50 dark:border-slate-700/50 uppercase tracking-widest truncate max-w-[120px]" title="{{ $this->flatAvailableEvents[$event] ?? $event }}">
                                            {{ $this->flatAvailableEvents[$event] ?? $event }}
                                        </span>
                                    @endif
                                @endforeach
                                @if(count($ep->events) > 3)
                                    <span class="text-[9px] font-black bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2 py-1 rounded border border-indigo-100/50 dark:border-indigo-800/30 uppercase tracking-widest">
                                        +{{ count($ep->events) - 3 }} more
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top text-right whitespace-nowrap">
                            <div class="flex flex-col items-end gap-2">
                                <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-1 border border-slate-200/50 dark:border-slate-700/50">
                                    <button wire:click="openModal({{ $ep->id }}, 'outbound')" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="Edit Endpoint">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <a href="{{ route('settings.webhooks.logs', ['endpointId' => $ep->id]) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="View Logs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                    </a>
                                    <div class="w-px h-4 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                                    <button wire:click="toggleStatus({{ $ep->id }}, 'outbound')" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="{{ $ep->is_active ? 'Pause' : 'Resume' }}">
                                        @if($ep->is_active)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    <button wire:confirm="Permanent deletion of this endpoint?" wire:click="delete({{ $ep->id }}, 'outbound')" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="Delete">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <select wire:model.live="selectedTestEvent" class="w-32 text-[9px] font-black uppercase tracking-widest bg-transparent border-none focus:ring-0 text-slate-500 py-1 cursor-pointer truncate text-right appearance-none" style="text-align-last:right;">
                                        @foreach($availableEvents as $category => $events)
                                            <optgroup label="{{ $category }}">
                                                @foreach($events as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <button wire:click="testEndpoint({{ $ep->id }})" wire:loading.attr="disabled" class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest hover:underline disabled:opacity-50 flex items-center gap-1">
                                        <span wire:loading.remove wire:target="testEndpoint({{ $ep->id }})">Ping</span>
                                        <span wire:loading wire:target="testEndpoint({{ $ep->id }})" class="animate-pulse">Ping...</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-24 flex flex-col items-center justify-center text-center px-4">
            <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/20 rounded-[1.5rem] shadow-inner flex items-center justify-center mb-5 transform rotate-3 border border-indigo-100 dark:border-indigo-800/30">
                <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">No Webhooks Yet</h3>
            <p class="text-sm font-medium text-slate-500 max-w-sm">Connect external APIs, CRMs, or analytics platforms to instantly stream real-time data from HMS.</p>
            <button wire:click="openModal(null, 'outbound')" class="mt-6 px-6 py-2.5 bg-slate-900 dark:bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.15em] hover:scale-105 transition-all shadow-lg shadow-slate-900/20 dark:shadow-indigo-500/20">
                Add First Endpoint
            </button>
        </div>
        @endif
    </div>
    @endif

    <!-- Inbound Content (Table Format) -->
    @if($activeTab === 'inbound')
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        @if($sources->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Source Configuration</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Authentication</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($sources as $src)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                        <td class="px-6 py-5 align-top w-1/3">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 shrink-0">
                                    <div class="w-8 h-8 rounded-xl {{ $src->is_active ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 border border-emerald-100 dark:border-emerald-800/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }} flex items-center justify-center shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors cursor-pointer" wire:click="openModal({{ $src->id }}, 'inbound')">{{ $src->name }}</h3>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <code class="text-[9px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200/50 dark:border-slate-700/50 text-indigo-600 dark:text-indigo-400 font-bold truncate max-w-full">
                                            /api/v1/webhooks/{{ $src->slug }}
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top whitespace-nowrap">
                            @if($src->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Listening
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Disabled
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-widest border {{ $src->auth_type === 'open' ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800/50' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50' }}">
                                @if($src->auth_type === 'secret')
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    HMAC Secret
                                @elseif($src->auth_type === 'bearer')
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                    Bearer Token
                                @else
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                                    No Auth
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-5 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-1 border border-slate-200/50 dark:border-slate-700/50">
                                    <button wire:click="openModal({{ $src->id }}, 'inbound')" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="Edit Source">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <a href="{{ route('settings.webhooks.inbound', ['sourceSlug' => $src->slug]) }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="View Logs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                    </a>
                                    <div class="w-px h-4 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                                    <button wire:click="toggleStatus({{ $src->id }}, 'inbound')" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="{{ $src->is_active ? 'Disable' : 'Enable' }}">
                                        @if($src->is_active)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        @endif
                                    </button>
                                    <button wire:confirm="Permanent deletion of this source?" wire:click="delete({{ $src->id }}, 'inbound')" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all" title="Delete">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-24 flex flex-col items-center justify-center text-center px-4">
            <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-900/20 rounded-[1.5rem] shadow-inner flex items-center justify-center mb-5 transform -rotate-3 border border-emerald-100 dark:border-emerald-800/30">
                <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mb-2">No Inbound Sources</h3>
            <p class="text-sm font-medium text-slate-500 max-w-sm">Create inbound sources to securely receive data from external systems like CRMs or IoT devices.</p>
            <button wire:click="openModal(null, 'inbound')" class="mt-6 px-6 py-2.5 bg-slate-900 dark:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.15em] hover:scale-105 transition-all shadow-lg shadow-slate-900/20 dark:shadow-emerald-500/20">
                Create First Source
            </button>
        </div>
        @endif
    </div>
    @endif

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white dark:bg-slate-900 w-full max-w-3xl max-h-[90vh] rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-300 border border-slate-200 dark:border-slate-800">
                <!-- Modal Header -->
                <div class="px-10 py-8 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 shrink-0 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <h2 class="text-2xl font-black uppercase tracking-tight text-slate-900 dark:text-white">
                                {{ $activeTab === 'outbound' ? ($editingEndpointId ? 'Edit Endpoint' : 'New Integration') : ($editingSourceId ? 'Edit Source' : 'New Inbound Source') }}
                            </h2>
                            <p class="text-[11px] text-slate-500 mt-1.5 uppercase tracking-[0.2em] font-black">
                                {{ $activeTab === 'outbound' ? 'Connect HMS to external APIs' : 'Receive data from external services' }}
                            </p>
                        </div>
                        <button wire:click="$set('showModal', false)" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <form wire:submit="save" id="webhookForm" class="p-10 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <x-form.input label="Display Name" wire:model="name" placeholder="e.g. CRM Sync or Stripe Inbound" />
                            
                            @if($activeTab === 'outbound')
                                <x-form.input label="Target URL" wire:model="url" placeholder="https://api.yourcrm.com/v1/hms-hook" />
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">API Version</label>
                                    <select wire:model="apiVersion" class="block w-full md:w-1/2 px-4 py-3 rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-sm font-bold text-slate-700 dark:text-slate-200 focus:ring-0 focus:border-indigo-500 transition-colors">
                                        <option value="v1">v1 (Current Stable)</option>
                                        <option value="2026-05">2026-05 (Beta Features)</option>
                                    </select>
                                </div>
                            @else
                                <x-form.input label="Endpoint Slug" wire:model="slug" placeholder="e.g. stripe-payments" />
                            @endif
                        </div>

                        @if($activeTab === 'inbound')
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Authentication Type</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach(['secret' => 'HMAC Secret', 'bearer' => 'Bearer Token', 'open' => 'No Auth (Open)'] as $val => $lbl)
                                    <label class="flex flex-col items-center justify-center gap-3 p-5 rounded-[1.5rem] border-2 transition-all cursor-pointer relative overflow-hidden group {{ $authType === $val ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 bg-white dark:bg-slate-900' }}">
                                        @if($authType === $val)
                                            <div class="absolute top-0 right-0 p-2">
                                                <div class="w-2 h-2 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></div>
                                            </div>
                                        @endif
                                        <input type="radio" wire:model.live="authType" value="{{ $val }}" class="sr-only">
                                        <span class="text-xs font-black uppercase tracking-tight text-center {{ $authType === $val ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-400' }}">{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($activeTab === 'outbound')
                        <div class="space-y-5">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <label class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-[0.2em] block">Subscribe to Events</label>
                                <label class="flex items-center space-x-2 cursor-pointer bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                    <input type="checkbox" wire:click="toggleAllEvents" class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-[9px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest">Select All</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                                @foreach($availableEvents as $category => $events)
                                    <div class="space-y-4">
                                        <h4 class="text-[10px] font-black text-indigo-500 dark:text-indigo-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                            {{ $category }}
                                        </h4>
                                        <div class="space-y-3 pl-3">
                                            @foreach($events as $key => $label)
                                                <label class="flex items-center space-x-3 cursor-pointer group">
                                                    <input type="checkbox" wire:model="selectedEvents" value="{{ $key }}" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500 transition-colors">
                                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($authType !== 'open' || $activeTab === 'outbound')
                        <div x-data="{ showDocs: false }" class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-slate-200 dark:border-slate-700/50">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </div>
                                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest">
                                            {{ $authType === 'secret' || $activeTab === 'outbound' ? 'Secret Signing Key' : 'Bearer Token' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="showDocs = !showDocs" class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest hover:bg-indigo-50 dark:hover:bg-indigo-900/30 px-3 py-1.5 rounded-lg transition-colors border border-transparent hover:border-indigo-100 dark:hover:border-indigo-800">
                                            <span x-show="!showDocs">Show Setup Guide</span>
                                            <span x-show="showDocs">Hide Guide</span>
                                        </button>
                                        <button type="button" wire:click="$set('secret', '{{ Str::random(32) }}')" class="text-[10px] font-black text-slate-500 hover:text-slate-800 dark:hover:text-white uppercase tracking-widest bg-white dark:bg-slate-700 px-3 py-1.5 rounded-lg shadow-sm border border-slate-200 dark:border-slate-600 transition-all">Regenerate</button>
                                    </div>
                                </div>
                                
                                <div x-data="{ revealed: false }" class="relative group">
                                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4 pr-16 flex items-center">
                                        <code x-show="revealed" class="text-sm font-mono text-slate-800 dark:text-slate-200 break-all select-all">{{ $secret }}</code>
                                        <code x-show="!revealed" class="text-sm font-mono text-slate-400 tracking-widest">••••••••••••••••••••••••••••••••</code>
                                    </div>
                                    <button type="button" @click="revealed = !revealed" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 hover:text-indigo-600 bg-slate-100 dark:bg-slate-800 px-2.5 py-1.5 rounded-md uppercase tracking-wider transition-colors">
                                        <span x-show="!revealed">Reveal</span>
                                        <span x-show="revealed">Hide</span>
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-3 font-medium">
                                    {{ $authType === 'secret' || $activeTab === 'outbound' ? 'Used to generate HMAC-SHA256 signatures for every request.' : 'Must be provided in the Authorization header as a Bearer token.' }}
                                </p>
                            </div>

                            <!-- Developer Docs Snippet -->
                            <div x-show="showDocs" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-slate-900 rounded-[1.5rem] p-6 text-white font-mono text-xs space-y-4 overflow-hidden border border-slate-800 shadow-inner">
                                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                                    <span class="text-slate-400 uppercase tracking-widest font-black text-[10px]">Implementation Example (PHP)</span>
                                </div>
                                <pre class="overflow-x-auto text-slate-300 leading-relaxed text-[11px]"><code>$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HMS_SIGNATURE'];
$timestamp = $_SERVER['HTTP_X_HMS_TIMESTAMP'];

<span class="text-slate-500">// Verify signature using the secret key</span>
$expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $payload, '{{ $secret }}');

if (hash_equals($expected, $signature)) {
    <span class="text-emerald-400">// Verified: Request is authentic</span>
    $data = json_decode($payload, true);
}</code></pre>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-10 py-6 border-t border-slate-100 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/20 flex justify-end items-center gap-4 shrink-0">
                    <button type="button" wire:click="$set('showModal', false)" class="px-6 py-3 text-[11px] font-black text-slate-500 hover:text-slate-800 dark:hover:text-white uppercase tracking-widest transition-colors">Cancel</button>
                    <button type="submit" form="webhookForm" wire:loading.attr="disabled" class="px-8 py-3 bg-slate-900 dark:bg-indigo-600 text-white rounded-xl text-[11px] font-black uppercase tracking-[0.15em] hover:scale-105 hover:shadow-lg hover:shadow-indigo-500/20 transition-all disabled:opacity-50 disabled:hover:scale-100">
                        <span wire:loading.remove wire:target="save">
                            {{ ($editingEndpointId || $editingSourceId) ? 'Save Changes' : 'Create Integration' }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
