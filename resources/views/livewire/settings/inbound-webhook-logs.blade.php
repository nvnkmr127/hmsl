<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Inbound Audit Trail</h3>
                <p class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em] mt-1">Monitor and debug incoming data streams</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search correlation, source, ID..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-2 border-slate-100 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 focus:border-indigo-500 focus:ring-0 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <select wire:model.live="status" class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-2 border-slate-100 dark:border-slate-800 rounded-xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:border-indigo-500 focus:ring-0 transition-all appearance-none pr-10 cursor-pointer">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>

                @if($sourceSlug)
                    <div class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 px-4 py-2.5 rounded-xl border border-indigo-100 dark:border-indigo-800/50">
                        <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Source: {{ $sourceSlug }}</span>
                        <button wire:click="$set('sourceSlug', null)" class="text-indigo-400 hover:text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Received At</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Source & ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Verification</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($logs as $log)
                        <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-slate-900 dark:text-white tracking-tight">{{ $log->created_at->format('H:i:s') }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">{{ $log->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 text-[9px] font-black rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase tracking-widest border border-indigo-100 dark:border-indigo-800/30">
                                        {{ $log->source }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400 truncate max-w-[120px]">{{ $log->external_id ?? 'No Ext ID' }}</span>
                                </div>
                                <div class="text-[9px] font-mono text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">CID: {{ substr($log->correlation_id, 0, 8) }}...</div>
                            </td>
                            <td class="px-6 py-5">
                                @if($log->is_verified)
                                    <span class="inline-flex items-center gap-1 text-emerald-500">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Authentic</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Unverified</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-2.5 py-1 text-[9px] font-black rounded-full 
                                    {{ $log->status === 'completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 
                                       ($log->status === 'failed' ? 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400') }} uppercase tracking-widest">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="replay({{ $log->id }})" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all" title="Replay (Clones with new CID)">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m0 0l-4-4m4 4l-4 4m0 6H8m0 0l4 4m-4-4l4-4" /></svg>
                                        </button>
                                        @if($log->status === 'failed')
                                            <button wire:click="retryProcessing({{ $log->id }})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all" title="Retry Processing">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                            </button>
                                        @endif
                                        <button wire:click="showDetails({{ $log->id }})" class="px-4 py-2 bg-slate-900 dark:bg-indigo-600/10 text-white dark:text-indigo-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:scale-105 transition-all">Audit</button>
                                    </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    </div>
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">No Inbound Logs Found</h4>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Try adjusting your filters or search terms</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-8 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Inbound Details Modal (Keep existing but polish) -->
    @if($selectedLog)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white dark:bg-slate-900 w-full max-w-5xl max-h-[90vh] rounded-[3rem] shadow-2xl overflow-hidden flex flex-col animate-in zoom-in-95 duration-300 border border-slate-200 dark:border-slate-800">
                <div class="p-10 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Deep Audit: Inbound Event</h3>
                        <p class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em] mt-1.5">Source: {{ $selectedLog->source }} • Received: {{ $selectedLog->created_at->format('M d, Y @ H:i:s') }}</p>
                    </div>
                    <button wire:click="closeDetails" class="relative z-10 p-3 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:scale-105">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-10 space-y-10">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Process Status</span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $selectedLog->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $selectedLog->status }}
                            </span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Verification</span>
                            <span class="text-xs font-black uppercase tracking-widest {{ $selectedLog->is_verified ? 'text-emerald-500' : 'text-slate-400' }}">
                                {{ $selectedLog->is_verified ? 'PASS (AUTHENTIC)' : 'FAIL / OPEN' }}
                            </span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Attempts</span>
                            <span class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $selectedLog->attempt_count }} tries</span>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm md:col-span-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Correlation ID</span>
                            <code class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400 truncate block">{{ $selectedLog->correlation_id ?? 'N/A' }}</code>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <div class="space-y-4">
                            <h4 class="text-[11px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-[0.2em] flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
                                HTTP Headers
                            </h4>
                            <div class="bg-slate-900 rounded-[2rem] p-8 border border-slate-800 shadow-inner group relative">
                                <pre class="text-[11px] font-mono text-slate-400 overflow-x-auto custom-scrollbar leading-relaxed">@json($selectedLog->headers, JSON_PRETTY_PRINT)</pre>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-[11px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-[0.2em] flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                Event Payload
                            </h4>
                            <div class="bg-slate-900 rounded-[2rem] p-8 border border-slate-800 shadow-inner relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="text-[9px] font-black text-indigo-400 uppercase tracking-widest hover:text-white">Copy JSON</button>
                                </div>
                                <pre class="text-[11px] font-mono text-indigo-300 overflow-x-auto custom-scrollbar leading-relaxed">@json($selectedLog->payload, JSON_PRETTY_PRINT)</pre>
                            </div>
                        </div>
                    </div>

                    @if($selectedLog->error_message)
                        <div class="space-y-4 animate-in slide-in-from-bottom-4 duration-500">
                            <h4 class="text-[11px] font-black text-rose-500 uppercase tracking-[0.2em] flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div>
                                Processing Failure Logs
                            </h4>
                            <div class="bg-rose-50/50 dark:bg-rose-950/20 rounded-[2rem] p-8 border border-rose-100 dark:border-rose-900/30">
                                <div class="flex items-start gap-4">
                                    <div class="mt-1 shrink-0 w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center text-rose-600">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-xs font-bold text-rose-900 dark:text-rose-200 uppercase tracking-tight">Exception Caught during execution:</p>
                                        <p class="text-xs font-mono text-rose-600 dark:text-rose-400 leading-relaxed">{{ $selectedLog->error_message }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="p-10 bg-slate-50/80 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-4">
                        @if($selectedLog->status === 'failed')
                            <button wire:click="retryProcessing({{ $selectedLog->id }})" class="px-8 py-3 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-widest rounded-2xl hover:scale-105 transition-all shadow-lg shadow-indigo-500/20 active:scale-95">Retry Processing</button>
                        @endif
                    </div>
                    <button wire:click="closeDetails" class="px-8 py-3 bg-slate-900 dark:bg-slate-700 text-white text-[11px] font-black uppercase tracking-widest rounded-2xl hover:bg-slate-800 dark:hover:bg-slate-600 transition-all active:scale-95">Dismiss Audit</button>
                </div>
            </div>
        </div>
    @endif
</div>
