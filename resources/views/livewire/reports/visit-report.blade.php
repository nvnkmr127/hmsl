<div class="space-y-6">
    {{-- Filters Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">From Date</label>
                <input wire:model.live="dateFrom" type="date" class="block w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary-500 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">To Date</label>
                <input wire:model.live="dateTo" type="date" class="block w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary-500 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Visit Status</label>
                <select wire:model.live="status" class="block w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                    <option value="all">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="overflow-hidden border border-slate-200 dark:border-slate-800 rounded-3xl bg-white dark:bg-slate-900 shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Date / Token</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Patient</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Doctor / Service</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Fee</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($visits as $visit)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $visit->consultation_date->format('d M, Y') }}</div>
                        <div class="text-[10px] font-black text-primary-500 uppercase tracking-widest mt-0.5">Token #{{ $visit->token_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $visit->patient->full_name }}</div>
                        <div class="text-[10px] text-slate-500">{{ $visit->patient->uhid }} | {{ $visit->patient->phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-700 dark:text-slate-300">Dr. {{ $visit->doctor->full_name ?? '—' }}</div>
                        <div class="text-[10px] text-slate-500 uppercase font-medium">{{ $visit->service->name ?? 'OPD Consultation' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-white">
                        ₹{{ number_format($visit->fee, 0) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                            @if($visit->status === 'Completed') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                            @elseif($visit->status === 'Cancelled') bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400
                            @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                            {{ $visit->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm font-medium">No visits found for the selected period.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $visits->links() }}
    </div>
</div>
