<div class="space-y-8">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 lg:p-8 shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
        <!-- Top Row: Title, Date Basis, Date Range, Search & Reset -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">OPD Visit Intelligence</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Traceable Outpatient Consultations, Revisit Patterns & Fee Collections</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Basis Toggle -->
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <button type="button" wire:click="$set('dateBasis', 'visit')"
                            class="px-3 py-1.5 text-[10px] font-black uppercase rounded-lg transition-all {{ $dateBasis === 'visit' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Visit Date
                    </button>
                    <button type="button" wire:click="$set('dateBasis', 'billing')"
                            class="px-3 py-1.5 text-[10px] font-black uppercase rounded-lg transition-all {{ $dateBasis === 'billing' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Billing Date
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
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Search Patient / Token</label>
                <input type="text" wire:model.live.debounce.350ms="search"
                       placeholder="Search token #, name, UHID, phone..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Visit Type</label>
                <select wire:model.live="visitType" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Types</option>
                    <option value="New">New Patient</option>
                    <option value="Follow-up">Follow-up / Review</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Visit Status</label>
                <select wire:model.live="status" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Statuses</option>
                    <option value="Completed">Completed</option>
                    <option value="Pending">Pending</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Billing Status</label>
                <select wire:model.live="billingStatus" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Billing</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Not Billed">Not Billed</option>
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Doctor / Dept</label>
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
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total OPD Visits</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ number_format($stats['summary']['total_visits']) }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">In Selected Period</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">New Patient Visits</p>
            <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ number_format($stats['summary']['new_visits']) }}</p>
            <p class="text-[10px] font-bold text-blue-500 mt-1 uppercase">First-time Patients</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Revisit / Follow-up Rate</p>
            <p class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ $stats['summary']['revisit_rate'] }}%</p>
            <p class="text-[10px] font-bold text-purple-500 mt-1 uppercase">{{ number_format($stats['summary']['revisits']) }} Revisits</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gross Consultation Fees</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['total_fees'], 2) }}</p>
            <p class="text-[10px] font-bold text-amber-500 mt-1 uppercase">Discount: ₹{{ number_format($stats['summary']['total_discounts'], 2) }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Fees Collected</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($stats['summary']['total_collected'], 2) }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Realized Revenue</p>
        </div>
    </div>

    <!-- Advanced Operational Insights -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Peak Traffic Day</p>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['summary']['busiest_day'] }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">Busiest day of week</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Peak Rush Hour</p>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $stats['summary']['peak_hour'] }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">Highest consultation density</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Daily Footfall</p>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['summary']['avg_daily_visits'] }}</p>
            <p class="text-[10px] font-bold text-amber-500 mt-1 uppercase">Visits per active day</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Top Department</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 truncate">{{ $stats['summary']['top_department'] }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Highest volume specialty</p>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Visit Velocity</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daily outpatient Consultation volume flow</p>
            </div>
            <x-chart type="line" :data="$stats['daily_trend']" id="visit-trend-chart" label="Visits" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Consultation Share</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Visits distributed by physician</p>
            </div>
            <x-chart type="bar" :data="$stats['doctor_wise']" id="doctor-share-chart" label="Patients Seen" />
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Day-Wise Volume</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Outpatient traffic distributed by day of the week</p>
            </div>
            <x-chart type="bar" :data="$stats['day_wise']" id="day-wise-chart" label="Visits" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Peak Consultation Hours</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Patient check-in density by hour of day</p>
            </div>
            <x-chart type="line" :data="$stats['peak_hours']" id="peak-hours-chart" label="Visits" />
        </div>
    </div>

    <!-- Datatable -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Traceable Patient Visit Records</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Click patient name or bill for complete history</p>
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
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Token & Time</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Doctor & Dept</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Visit Type & Fee</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Billing & Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($visits as $visit)
                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                Token #{{ $visit->token_number ?? '—' }}
                            </div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1">
                                {{ optional($visit->consultation_date)->format('d M Y') ?? 'N/A' }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ optional($visit->consultation_time)->format('h:i A') ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($visit->patient)
                                <a href="{{ route('counter.patients.history', $visit->patient->id) }}" 
                                   class="text-xs font-black text-indigo-600 hover:underline dark:text-indigo-400 block truncate">
                                    {{ $visit->patient->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">UHID: {{ $visit->patient->uhid ?? '—' }}</div>
                                @if($visit->patient->phone)
                                    <div class="text-[10px] font-semibold text-slate-400">Ph: {{ $visit->patient->phone }}</div>
                                @endif
                            @else
                                <span class="text-xs font-bold text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ optional($visit->doctor)->full_name ?? 'N/A' }}</div>
                            <div class="text-[10px] font-semibold text-slate-400">{{ optional(optional($visit->doctor)->department)->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 mb-1">
                                @if($visit->visit_type === 'New')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400">New</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400">{{ $visit->visit_type }}</span>
                                @endif
                            </div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">₹{{ number_format($visit->fee, 2) }}</div>
                            @if($visit->discount_amount > 0)
                                <div class="text-[10px] font-bold text-amber-500">Disc: ₹{{ number_format($visit->discount_amount, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $visit->status === 'Completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/30' }}">
                                    {{ $visit->status }}
                                </span>
                                @if($visit->bill)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $visit->bill->payment_status === 'Paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $visit->bill->payment_status }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-500">Not Billed</span>
                                @endif
                            </div>
                            @if($visit->bill)
                                <a href="{{ route('billing.bills.print', $visit->bill->id) }}" target="_blank" class="font-mono text-[10px] font-bold text-indigo-600 hover:underline">
                                    {{ $visit->bill->bill_number }}
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($visit->patient)
                                    <a href="{{ route('counter.patients.history', $visit->patient->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 text-xs font-bold transition-all">
                                        History
                                    </a>
                                @endif
                                @if($visit->bill)
                                    <a href="{{ route('billing.bills.print', $visit->bill->id) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 hover:bg-indigo-100 text-xs font-bold transition-all">
                                        Bill
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">No OPD visits found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            {{ $visits->links() }}
        </div>
    </div>
</div>
