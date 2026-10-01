<div class="space-y-8">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 lg:p-8 shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
        <!-- Top Row: Title, Date Range, Search & Reset -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Executive Accounts Receivable & Outstanding Dues</h1>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">Traceable Patient Balances, Aging Analysis, Recovery Potential & Debt Attribution</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Range -->
                <div class="flex items-center gap-2 bg-slate-100/70 dark:bg-slate-800/70 px-3 h-[42px] rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase">From</span>
                        <input type="date" wire:model.live="from" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 h-4">
                    </div>
                    <span class="text-slate-500 dark:text-slate-300">•</span>
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase">To</span>
                        <input type="date" wire:model.live="to" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 h-4">
                    </div>
                </div>

                <button wire:click="resetFilters" 
                        class="h-[42px] px-3 flex items-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-rose-500 hover:bg-rose-50 text-xs font-bold transition-all" title="Clear Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Bottom Row: Search & Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div class="lg:col-span-6">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Search Patient / Bill</label>
                <input type="text" wire:model.live.debounce.350ms="search"
                       placeholder="Search bill #, patient name, UHID..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Department</label>
                <select wire:model.live="departmentId" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Doctor</label>
                <select wire:model.live="doctorId" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Outstanding</p>
            <p class="text-3xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($stats['summary']['total_outstanding'], 2) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">Cumulative Receivables</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Billed</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['total_billed'], 2) }}</p>
            <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400 mt-1 uppercase">Gross Invoice Value</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Paid</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($stats['summary']['total_paid'], 2) }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Realized Collections</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Patients With Dues</p>
            <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($stats['summary']['patients_count']) }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">Active Debtor Accounts</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Collection Efficiency</p>
            <p class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ $stats['summary']['collection_rate'] }}%</p>
            <p class="text-[10px] font-bold text-purple-500 mt-1 uppercase">Recovery Rate</p>
        </div>
    </div>

    <!-- Advanced Operational Insights -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Highest Debtor Account</p>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400 truncate">{{ $stats['summary']['highest_debtor_name'] }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">₹{{ number_format($stats['summary']['highest_debtor_amount'], 2) }} Outstanding</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Oldest Outstanding</p>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['summary']['oldest_days'] }} Days</p>
            <p class="text-[10px] font-bold text-amber-500 mt-1 uppercase">Maximum bill aging</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Critical Overdue Bills</p>
            <p class="text-2xl font-black text-red-600 dark:text-red-400">{{ $stats['summary']['critical_count'] }}</p>
            <p class="text-[10px] font-bold text-red-500 mt-1 uppercase">Invoices over 30 days old</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Avg. Due Per Account</p>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">₹{{ number_format($stats['summary']['avg_due'], 2) }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">Mean patient balance</p>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Aging Breakdown (Volume)</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Outstanding balance grouped by invoice maturity</p>
            </div>
            <x-chart type="bar" :data="$stats['aging_amounts']" id="aging-amounts-chart" label="Amount Due (₹)" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Aging Distribution (Share)</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Proportional share of debt across time buckets</p>
            </div>
            <x-chart type="doughnut" :data="$stats['aging_amounts']" id="aging-share-chart" label="Amount Due (₹)" />
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Department-Wise Receivables</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Outstanding balances attributed by specialty</p>
            </div>
            <x-chart type="bar" :data="$stats['department_wise']" id="dept-dues-chart" label="Outstanding (₹)" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Doctor-Wise Dues Attribution</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Uncollected patient balances by attending doctor</p>
            </div>
            <x-chart type="bar" :data="$stats['doctor_wise']" id="doctor-dues-chart" label="Outstanding (₹)" />
        </div>
    </div>

    <!-- Datatable -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Traceable Outstanding Accounts Ledger</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-0.5">Click patient name or settle button to manage invoice</p>
            </div>
            <button wire:click="exportCsv" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 dark:bg-slate-800/60">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Bill # & Date</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Patient Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Doctor & Dept</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Financials</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Aging & Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($dues as $due)
                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $due->bill_number ?? '—' }}
                            </div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1">
                                {{ optional($due->created_at)->format('d M Y') ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($due->patient)
                                <a href="{{ route('counter.patients.history', $due->patient->id) }}" 
                                   class="text-xs font-black text-indigo-600 hover:underline dark:text-indigo-400 block truncate">
                                    {{ $due->patient->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-600 dark:text-slate-400 font-mono mt-0.5">UHID: {{ $due->patient->uhid ?? '—' }}</div>
                                @if($due->patient->phone)
                                    <div class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Ph: {{ $due->patient->phone }}</div>
                                @endif
                            @else
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ optional(optional($due->consultation)->doctor)->full_name ?? 'N/A' }}</div>
                            <div class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{ optional(optional(optional($due->consultation)->doctor)->department)->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-xs font-black text-rose-600 dark:text-rose-400">₹{{ number_format($due->balance_amount, 2) }} Due</div>
                            <div class="text-[10px] font-semibold text-slate-600 dark:text-slate-400">Paid: ₹{{ number_format($due->paid_amount, 2) }} / ₹{{ number_format($due->total_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $days = $due->created_at ? $due->created_at->diffInDays(now()) : 0;
                            @endphp
                            <div class="flex items-center gap-1.5 mb-1">
                                @if($days <= 7)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">0-7 Days</span>
                                @elseif($days <= 30)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400">8-30 Days</span>
                                @elseif($days <= 90)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-400">31-90 Days</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">90+ Days</span>
                                @endif
                            </div>
                            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ $due->payment_status }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('billing.index', ['patientId' => $due->patient_id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 text-xs font-bold rounded-xl transition-all">
                                <span>Settle Due</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs font-bold uppercase tracking-widest">No Outstanding Dues Found</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dues->hasPages())
        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            {{ $dues->links() }}
        </div>
        @endif
    </div>
</div>
