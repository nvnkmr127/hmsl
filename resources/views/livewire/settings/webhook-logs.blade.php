<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Delivery Logs</h2>
            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-1">Audit trail for all webhook attempts</p>
        </div>
        
        <div class="flex items-center space-x-3">
            <input 
                wire:model.live="search" 
                type="text" 
                placeholder="Search logs..." 
                class="bg-white dark:bg-gray-800 border-none rounded-2xl px-6 py-2.5 text-xs font-medium shadow-sm focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-gray-200"
            >
            <x-form.select wire:model.live="statusFilter" class="w-32 py-2">
                <option value="">All Status</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="retrying">Retrying</option>
            </x-form.select>
        </div>
    </div>

    <x-card>
        <x-table.wrapper>
            <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-900/40">
                    <x-table.th>Event & Status</x-table.th>
                    <x-table.th>Endpoint</x-table.th>
                    <x-table.th>Response</x-table.th>
                    <x-table.th>Attempts</x-table.th>
                    <x-table.th class="text-right">Delivered At</x-table.th>
                    <x-table.th></x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $log->event_name }}</span>
                                <div class="mt-1 flex items-center space-x-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $log->status === 'success' ? 'bg-emerald-500' : ($log->status === 'failed' ? 'bg-rose-500' : 'bg-amber-500') }}"></div>
                                    <span class="text-[8px] font-black uppercase tracking-widest {{ $log->status === 'success' ? 'text-emerald-600' : ($log->status === 'failed' ? 'text-rose-600' : 'text-amber-600') }}">
                                        {{ $log->status }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-400 capitalize">{{ $log->endpoint?->name ?? 'Unknown endpoint' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->response_status)
                                <x-badge color="{{ $log->response_status >= 200 && $log->response_status < 300 ? 'emerald' : 'rose' }}">
                                    HTTP {{ $log->response_status }}
                                </x-badge>
                            @else
                                <span class="text-[10px] text-rose-500 font-bold italic">Network Error</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-black text-gray-400">#{{ $log->attempt_number }} / 5</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $log->delivered_at ? $log->delivered_at->format('d M, H:i') : '--' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($log->status === 'failed')
                                <button wire:click="retry({{ $log->id }})" class="p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors" title="Retry Manually">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No webhook activity logged yet." />
                @endforelse
            </tbody>
        </x-table.wrapper>
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </x-card>
</div>
