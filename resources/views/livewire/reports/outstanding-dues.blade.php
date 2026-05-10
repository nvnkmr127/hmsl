<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[3rem] p-10 shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Outstanding Receivables</h1>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mt-2">Monitoring unsettled patient accounts</p>
                </div>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search Patient / UHID / Bill..." class="pl-12 pr-6 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-rose-500/20 transition-all w-full md:w-72">
                    <svg class="w-5 h-5 text-slate-400 absolute left-4 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient Details</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Bill #</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Balance</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @foreach($dues as $due)
                        <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-slate-900 dark:text-white">{{ $due->patient?->full_name }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $due->patient?->uhid }}</div>
                            </td>
                            <td class="px-8 py-5 text-[11px] font-mono font-bold text-slate-500 uppercase">{{ $due->bill_number }}</td>
                            <td class="px-8 py-5 text-right">
                                <span class="text-sm font-black text-rose-600">₹{{ number_format($due->balance_amount, 2) }}</span>
                                <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Total: ₹{{ number_format($due->total_amount, 2) }}</div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <a href="{{ route('billing.index', ['patientId' => $due->patient_id]) }}" class="inline-flex items-center gap-2 text-xs font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-colors">
                                    Settle
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-8">
                {{ $dues->links() }}
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-slate-900 dark:bg-white p-10 rounded-[3rem] shadow-xl shadow-slate-900/20 dark:shadow-white/5 relative overflow-hidden">
                <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/5 dark:bg-slate-900/5 rounded-full blur-2xl translate-y-1/2 translate-x-1/2"></div>
                <div class="relative z-10">
                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] block mb-4">Cumulative Debt</span>
                    <div class="text-5xl font-black text-white dark:text-slate-900 tracking-tighter">₹{{ number_format($totalOutstanding, 2) }}</div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        <p class="text-[10px] font-bold text-rose-400 dark:text-rose-600 uppercase tracking-widest">Requires Attention</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight mb-8">Aging Summary</h3>
                <div class="space-y-6">
                    @foreach(['0-7 Days' => 45, '8-30 Days' => 30, '31-90 Days' => 15, '90+ Days' => 10] as $bucket => $perc)
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $bucket }}</span>
                            <span class="text-[10px] font-black text-slate-600 dark:text-slate-300">{{ $perc }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $perc }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
