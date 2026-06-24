<div class="space-y-10">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Revenue Analytics</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mt-2">Financial performance and collection health</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <input type="date" wire:model.live="from" class="bg-transparent border-none text-xs font-black uppercase text-slate-600 dark:text-slate-300 focus:ring-0 cursor-pointer">
                    <span class="text-slate-300">→</span>
                    <input type="date" wire:model.live="to" class="bg-transparent border-none text-xs font-black uppercase text-slate-600 dark:text-slate-300 focus:ring-0 cursor-pointer">
                </div>

                <select wire:model.live="paymentMethod" class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm group hover:border-emerald-500/30 transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Gross Collection</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">₹{{ number_format($stats['summary']['gross_collection'], 2) }}</div>
            <div class="mt-2 text-[10px] font-bold text-emerald-500 uppercase">Total Payments Received</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm group hover:border-rose-500/30 transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Refunds</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">₹{{ number_format($stats['summary']['total_refunds'], 2) }}</div>
            <div class="mt-2 text-[10px] font-bold text-rose-500 uppercase">Revenue Reversals</div>
        </div>

        <div class="bg-slate-900 dark:bg-white p-8 rounded-[2.5rem] border border-slate-800 dark:border-slate-200 shadow-xl shadow-slate-900/20 dark:shadow-white/5 group transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-white/10 dark:bg-slate-100 flex items-center justify-center text-white dark:text-slate-900">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Net Collection</span>
            </div>
            <div class="text-4xl font-black text-white dark:text-slate-900 tracking-tighter">₹{{ number_format($stats['summary']['net_collection'], 2) }}</div>
            <div class="mt-2 text-[10px] font-bold text-emerald-400 dark:text-emerald-600 uppercase tracking-widest">Final Realized Revenue</div>
        </div>
    </div>

    @php
        $dailyNet = collect($stats['daily_trend'])->pluck('net', 'date')->toArray();
        $dailyPatients = collect($stats['daily_trend'])->pluck('patients', 'date')->toArray();
    @endphp

    <!-- Daily Flow Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Daily Revenue Histogram -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Daily Net Revenue</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Histogram flow of income</p>
                </div>
            </div>
            <x-chart type="bar" :data="$dailyNet" id="daily-revenue-chart" label="Net Revenue (₹)" />
        </div>

        <!-- Daily Patient Flow Histogram -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Patient Flow</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Histogram flow of patients</p>
                </div>
            </div>
            <x-chart type="bar" :data="$dailyPatients" id="daily-patient-chart" label="Patients" />
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Revenue by Type -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Revenue Mix</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Income distribution by department</p>
                </div>
            </div>
            <x-chart type="doughnut" :data="$stats['revenue_by_type']" id="revenue-mix-chart" label="Revenue (₹)" />
        </div>

        <!-- Payment Methods -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Collection Channels</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Payment method utilization</p>
                </div>
            </div>
            <x-chart type="bar" :data="$stats['method_breakdown']" id="method-breakdown-chart" label="Total (₹)" />
        </div>
    </div>

    <!-- Day-wise Stats Table -->
    <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
        <div class="mb-8">
            <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Daily Performance Logs</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Detailed day-wise stats and amounts</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="py-4 px-4 text-xs font-black text-slate-500 uppercase tracking-widest">Date</th>
                        <th class="py-4 px-4 text-xs font-black text-slate-500 uppercase tracking-widest">Patients/Bills</th>
                        <th class="py-4 px-4 text-xs font-black text-slate-500 uppercase tracking-widest text-right">Gross (₹)</th>
                        <th class="py-4 px-4 text-xs font-black text-slate-500 uppercase tracking-widest text-right">Refunds (₹)</th>
                        <th class="py-4 px-4 text-xs font-black text-emerald-600 uppercase tracking-widest text-right">Net Collection (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($stats['daily_trend'] as $day)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="py-4 px-4 text-sm font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                        <td class="py-4 px-4 text-sm font-semibold text-slate-600 dark:text-slate-400">{{ $day['patients'] }}</td>
                        <td class="py-4 px-4 text-sm font-bold text-slate-700 dark:text-slate-300 text-right">{{ number_format($day['gross'], 2) }}</td>
                        <td class="py-4 px-4 text-sm font-bold text-rose-500 text-right">{{ number_format($day['refunds'], 2) }}</td>
                        <td class="py-4 px-4 text-sm font-black text-emerald-600 dark:text-emerald-500 text-right">{{ number_format($day['net'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-sm text-slate-500 font-semibold">No daily performance data found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
