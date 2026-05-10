<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-1 items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl leading-5 bg-white dark:bg-slate-800 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 ease-in-out sm:text-sm" placeholder="Search logs...">
            </div>
            
            <select wire:model.live="status" class="block w-40 pl-3 pr-10 py-2 text-base border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-xl bg-white dark:bg-slate-800 font-bold">
                <option value="all">All Status</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="retrying">Retrying</option>
            </select>

            <select wire:model.live="eventFilter" class="block w-48 pl-3 pr-10 py-2 text-base border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-xl bg-white dark:bg-slate-800 font-bold uppercase tracking-tight">
                <option value="all">All Events</option>
                @foreach($availableEventNames as $eventName)
                    <option value="{{ $eventName }}">{{ $eventName }}</option>
                @endforeach
            </select>

            <button wire:click="exportLogs" class="p-2 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-500 hover:text-indigo-600 transition-colors" title="Export CSV">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            </button>

            @if($endpointId)
                <div class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 px-4 py-2 rounded-xl border border-indigo-100 dark:border-indigo-800/50">
                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Filter: Endpoint #{{ $endpointId }}</span>
                    <button wire:click="$set('endpointId', null)" class="text-indigo-400 hover:text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            @if(count($selectedLogs) > 0)
                <button wire:click="bulkRetry" class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-500/20 flex items-center gap-2 animate-in slide-in-from-left duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Retry Selected ({{ count($selectedLogs) }})
                </button>
            @endif
        </div>
    </div>

    <div class="overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 rounded-2xl">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               x-on:change="$el.checked ? @this.set('selectedLogs', @js($logs->pluck('id')->toArray())) : @this.set('selectedLogs', [])">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timestamp</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Endpoint</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Event</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Duration</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors {{ in_array($log->id, $selectedLogs) ? 'bg-indigo-50/30' : '' }}">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <input type="checkbox" wire:model.live="selectedLogs" value="{{ $log->id }}" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                        {{ $log->created_at->format('M d, H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $log->endpoint->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ $log->endpoint->url ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200">
                            {{ $log->event_name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->status === 'success')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                {{ $log->response_status }} OK
                            </span>
                        @elseif($log->status === 'retrying')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                Retrying ({{ $log->attempt_number }})
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                {{ $log->response_status ?? 'Error' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 font-mono">
                        {{ $log->duration_ms ?? '--' }}ms
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-3">
                            <button wire:click="showDetails({{ $log->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold uppercase tracking-widest text-[10px]">Details</button>
                            <button wire:click="retry({{ $log->id }})" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 font-bold uppercase tracking-widest text-[10px]">Retry</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                        No delivery logs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>

    <!-- Details Modal -->
    @if($selectedLog)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white dark:bg-slate-900 w-full max-w-4xl max-h-[90vh] rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col animate-in zoom-in duration-300">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Audit Details</h3>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">{{ $selectedLog->event_name }} • {{ $selectedLog->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <button wire:click="closeDetails" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 space-y-8">
                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Endpoint</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $selectedLog->endpoint->name ?? 'Deleted' }}</span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Status Code</span>
                            <span class="text-sm font-bold {{ $selectedLog->status === 'success' ? 'text-emerald-500' : 'text-rose-500' }}">
                                {{ $selectedLog->response_status ?? 'Error' }}
                            </span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Duration</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $selectedLog->duration_ms ?? '--' }}ms</span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Category</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $selectedLog->error_category ?? 'SUCCESS' }}</span>
                        </div>
                    </div>

                    <!-- IDs Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-between">
                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Delivery ID</span>
                            <code class="text-[10px] font-mono text-indigo-600 dark:text-indigo-400">{{ $selectedLog->delivery_id ?? 'N/A' }}</code>
                        </div>
                        <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-between">
                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Correlation ID</span>
                            <code class="text-[10px] font-mono text-indigo-600 dark:text-indigo-400">{{ $selectedLog->correlation_id ?? 'N/A' }}</code>
                        </div>
                    </div>

                    <!-- JSON Sections -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Request Payload</h4>
                            <div class="bg-slate-900 rounded-[1.5rem] p-6 overflow-hidden">
                                <pre class="text-[11px] font-mono text-indigo-300 overflow-x-auto custom-scrollbar leading-relaxed">@json($selectedLog->payload, JSON_PRETTY_PRINT)</pre>
                            </div>
                        </div>

                        @if($selectedLog->response_body)
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Response Body</h4>
                            <div class="bg-slate-900 rounded-[1.5rem] p-6 overflow-hidden">
                                @if(is_array(json_decode($selectedLog->response_body, true)))
                                    <pre class="text-[11px] font-mono text-emerald-400 overflow-x-auto custom-scrollbar leading-relaxed">@json(json_decode($selectedLog->response_body, true), JSON_PRETTY_PRINT)</pre>
                                @else
                                    <pre class="text-[11px] font-mono text-slate-400 overflow-x-auto custom-scrollbar leading-relaxed whitespace-pre-wrap">{{ $selectedLog->response_body }}</pre>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($selectedLog->error_message)
                        <div>
                            <h4 class="text-xs font-black text-rose-400 uppercase tracking-[0.2em] mb-4">Error Log</h4>
                            <div class="bg-rose-50 dark:bg-rose-900/10 rounded-[1.5rem] p-6 border border-rose-100 dark:border-rose-900/30">
                                <p class="text-xs font-mono text-rose-600 dark:text-rose-400 leading-relaxed">{{ $selectedLog->error_message }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="p-8 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button wire:click="closeDetails" class="px-8 py-3 bg-slate-900 dark:bg-slate-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all active:scale-95">Close Audit</button>
                </div>
            </div>
        </div>
    @endif
</div>

