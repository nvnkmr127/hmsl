<div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Inbound Webhook History</h3>
        
        @if($sourceSlug)
            <div class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 px-4 py-2 rounded-xl border border-indigo-100 dark:border-indigo-800/50">
                <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Filter: {{ $sourceSlug }}</span>
                <button wire:click="$set('sourceSlug', null)" class="text-indigo-400 hover:text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Time</th>
                    <th class="px-6 py-4 font-semibold">Source</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $log->created_at->format('M d, H:i:s') }}
                            </span>
                            <div class="text-xs text-gray-400 font-mono">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase tracking-tight">
                                {{ $log->source }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg 
                                {{ $log->status === 'completed' ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 
                                   ($log->status === 'failed' ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400') }} uppercase tracking-tight">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button wire:click="showDetails({{ $log->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-sm font-bold uppercase tracking-widest text-[10px]">Details</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-6 border-t border-gray-100 dark:border-gray-700">
        {{ $logs->links() }}
    </div>

    <!-- Inbound Details Modal -->
    @if($selectedLog)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white dark:bg-slate-900 w-full max-w-4xl max-h-[90vh] rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col animate-in zoom-in duration-300">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Inbound Data Audit</h3>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Source: {{ $selectedLog->source }} • {{ $selectedLog->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <button wire:click="closeDetails" class="p-2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 space-y-8">
                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Process Status</span>
                            <span class="text-sm font-bold {{ $selectedLog->status === 'completed' ? 'text-emerald-500' : 'text-rose-500' }}">
                                {{ strtoupper($selectedLog->status) }}
                            </span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Processed At</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $selectedLog->processed_at ? $selectedLog->processed_at->format('H:i:s') : '--' }}</span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Correlation ID</span>
                            <span class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400 truncate block">{{ $selectedLog->correlation_id ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- JSON Sections -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Request Headers</h4>
                            <div class="bg-slate-900 rounded-[1.5rem] p-6 overflow-hidden">
                                <pre class="text-[11px] font-mono text-slate-400 overflow-x-auto custom-scrollbar leading-relaxed">@json($selectedLog->headers, JSON_PRETTY_PRINT)</pre>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Payload Data</h4>
                            <div class="bg-slate-900 rounded-[1.5rem] p-6 overflow-hidden">
                                <pre class="text-[11px] font-mono text-indigo-300 overflow-x-auto custom-scrollbar leading-relaxed">@json($selectedLog->payload, JSON_PRETTY_PRINT)</pre>
                            </div>
                        </div>

                        @if($selectedLog->error_message)
                        <div>
                            <h4 class="text-xs font-black text-rose-400 uppercase tracking-[0.2em] mb-4">Processing Error</h4>
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

