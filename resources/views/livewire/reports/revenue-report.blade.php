<div class="space-y-8">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 lg:p-8 shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
        <!-- Top Row: Title, Date Basis, Date Range, Search & Reset -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Executive Revenue Intelligence</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Real-Time Cash Flow, Conversions, Service Lines & Doctor Attribution</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Basis Toggle -->
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <button type="button" wire:click="$set('dateBasis', 'payment')"
                            class="px-3 py-1.5 text-[10px] font-black uppercase rounded-lg transition-all {{ $dateBasis === 'payment' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Payment Date
                    </button>
                    <button type="button" wire:click="$set('dateBasis', 'invoice')"
                            class="px-3 py-1.5 text-[10px] font-black uppercase rounded-lg transition-all {{ $dateBasis === 'invoice' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Invoice Date
                    </button>
                </div>

                <!-- Date Range -->
                <div class="flex items-center gap-2 bg-slate-100/70 dark:bg-slate-800/70 px-3 h-[42px] rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase">From</span>
                        <input type="date" wire:model.live="from" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 h-4">
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase">To</span>
                        <input type="date" wire:model.live="to" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 h-4">
                    </div>
                </div>

                <button wire:click="resetFilters" 
                        class="h-[42px] px-3 flex items-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-rose-500 hover:bg-rose-50 text-xs font-bold transition-all" title="Clear Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Bottom Row: Search & Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div class="lg:col-span-4">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Search Patient / Bill No</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.350ms="search"
                           placeholder="Search bill #, name, UHID, phone, reference..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Payment Method</label>
                <select wire:model.live="paymentMethod" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Transaction Type</label>
                <select wire:model.live="paymentType" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All Types</option>
                    <option value="payment">Collections</option>
                    <option value="refund">Refunds</option>
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Bill Category</label>
                <select wire:model.live="billCategory" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All Categories</option>
                    <option value="opd">OPD Consultations</option>
                    <option value="ipd">IPD Admissions</option>
                    <option value="direct">Direct Pharmacy / Counter</option>
                </select>
            </div>
        </div>
    </div>

    <!-- High-Level Strategic Ratios Bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-indigo-500/10 to-indigo-600/5 dark:from-indigo-950/40 dark:to-indigo-900/20 p-5 rounded-3xl border border-indigo-200/60 dark:border-indigo-800/60">
            <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Realization Conversion</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['summary']['realization_rate'] }}%</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Cash Realized</span>
            </div>
            <p class="text-[10px] font-medium text-slate-500 mt-1">Collected vs Gross Invoiced Amount</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 dark:from-emerald-950/40 dark:to-emerald-900/20 p-5 rounded-3xl border border-emerald-200/60 dark:border-emerald-800/60">
            <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">ARPP (Avg Per Patient)</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['arpp'], 0) }}</span>
            </div>
            <p class="text-[10px] font-medium text-slate-500 mt-1">Across {{ number_format($stats['summary']['unique_patients']) }} Unique Patients</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500/10 to-blue-600/5 dark:from-blue-950/40 dark:to-blue-900/20 p-5 rounded-3xl border border-blue-200/60 dark:border-blue-800/60">
            <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">Daily Revenue Velocity</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['daily_velocity'], 0) }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase">/ day</span>
            </div>
            <p class="text-[10px] font-medium text-slate-500 mt-1">Average over {{ $stats['summary']['days_count'] }} days</p>
        </div>

        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 dark:from-amber-950/40 dark:to-amber-900/20 p-5 rounded-3xl border border-amber-200/60 dark:border-amber-800/60">
            <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">Discount Leakage</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $stats['summary']['discount_leakage'] }}%</span>
            </div>
            <p class="text-[10px] font-medium text-slate-500 mt-1">Total Discount: ₹{{ number_format($stats['summary']['total_discounts'], 0) }}</p>
        </div>
    </div>

    <!-- Core Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gross Collections</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['gross_collection'], 2) }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Payments Received</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Refunds</p>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($stats['summary']['total_refunds'], 2) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">Revenue Reversals</p>
        </div>

        <div class="bg-slate-900 dark:bg-white p-5 rounded-3xl border border-slate-800 dark:border-slate-200 shadow-xl shadow-slate-900/10 relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Net Realized Revenue</p>
            <p class="text-2xl font-black text-white dark:text-slate-900">₹{{ number_format($stats['summary']['net_collection'], 2) }}</p>
            <p class="text-[10px] font-bold text-emerald-400 dark:text-emerald-600 mt-1 uppercase">Final Realized</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Billed</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['total_invoiced'], 2) }}</p>
            <p class="text-[10px] font-bold text-blue-500 mt-1 uppercase">Gross Invoiced</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Outstanding Dues</p>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($stats['summary']['total_dues'], 2) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">Uncollected Balance</p>
        </div>
    </div>

    <!-- Visual Charts Grid (3 Charts) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Revenue Mix</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">OPD vs IPD vs Direct Pharmacy</p>
            </div>
            <x-chart type="doughnut" :data="$stats['revenue_by_type']" id="revenue-mix-chart" label="Revenue (₹)" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Collection Channels</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Payment method distribution</p>
            </div>
            <x-chart type="bar" :data="$stats['method_breakdown']" id="method-breakdown-chart" label="Total (₹)" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Service Line Breakdown</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Consultations, Lab, Pharmacy, Beds</p>
            </div>
            <x-chart type="bar" :data="$stats['service_line']" id="service-line-chart" label="Income (₹)" />
        </div>
    </div>

    <!-- View Mode Selector & Datatables -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                <button type="button" wire:click="setViewMode('transactions')"
                        class="px-4 py-2 text-xs font-black uppercase rounded-xl transition-all {{ $viewMode === 'transactions' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Payment Log ({{ number_format($stats['summary']['total_transactions']) }})
                </button>
                <button type="button" wire:click="setViewMode('staff')"
                        class="px-4 py-2 text-xs font-black uppercase rounded-xl transition-all {{ $viewMode === 'staff' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Staff Collections
                </button>
                <button type="button" wire:click="setViewMode('intelligence')"
                        class="px-4 py-2 text-xs font-black uppercase rounded-xl transition-all {{ $viewMode === 'intelligence' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Top Doctors Attribution
                </button>
            </div>

            <button wire:click="exportCsv" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </button>
        </div>

        @if($viewMode === 'transactions')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-800/60">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date & Time</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Bill #</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient Details</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Method & Staff</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-900 dark:text-white">
                                    {{ optional($payment->received_at)->format('d M Y, h:i A') ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->bill)
                                    <a href="{{ route('billing.bills.print', $payment->bill->id) }}" target="_blank"
                                       class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $payment->bill->bill_number }}
                                    </a>
                                @else
                                    <span class="font-mono text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->bill && $payment->bill->patient)
                                    <a href="{{ route('counter.patients.history', $payment->bill->patient->id) }}"
                                       class="text-xs font-black text-slate-800 dark:text-slate-200 hover:text-indigo-600 block truncate">
                                        {{ $payment->bill->patient->full_name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-mono">UHID: {{ $payment->bill->patient->uhid ?? '—' }}</div>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">Direct Cash Customer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->bill && $payment->bill->consultation_id)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400">OPD</span>
                                @elseif($payment->bill && $payment->bill->admission_id)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400">IPD</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Direct</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50">
                                        {{ $payment->method ?? 'Cash' }}
                                    </span>
                                    @if($payment->type === 'refund')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">Refund</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">By: {{ optional($payment->receiver)->name ?? 'System' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-black {{ $payment->type === 'refund' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $payment->type === 'refund' ? '-' : '+' }}₹{{ number_format($payment->amount, 2) }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">No payment transactions found for the selected criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                {{ $payments->links() }}
            </div>
        @elseif($viewMode === 'staff')
            <div class="p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-800/60">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Cashier / Staff Name</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Transactions Processed</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Net Collections (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($stats['staff_collections'] as $staff)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 text-xs font-black text-slate-900 dark:text-white">
                                {{ optional($staff->receiver)->name ?? 'System / Automatic' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-center text-slate-600 dark:text-slate-400">
                                {{ $staff->tx_count }} Transactions
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-black text-emerald-600">
                                ₹{{ number_format($staff->net_collected, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-400 font-semibold">No cashier records found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($viewMode === 'intelligence')
            <div class="p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-800/60">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Doctor Name</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Bills Count</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Gross Billed (₹)</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Realized Collections (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($stats['top_doctors'] as $docName => $docData)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 text-xs font-black text-indigo-600 dark:text-indigo-400">
                                {{ $docName }}
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-center text-slate-600 dark:text-slate-400">
                                {{ $docData['count'] }} Bills
                            </td>
                            <td class="px-6 py-4 text-right text-xs font-bold text-slate-700 dark:text-slate-300">
                                ₹{{ number_format($docData['total'], 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-black text-emerald-600">
                                ₹{{ number_format($docData['paid'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400 font-semibold">No doctor revenue data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>


