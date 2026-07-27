<div class="space-y-8">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 lg:p-8 shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
        <!-- Top Row: Title, Date Basis, Date Range, Search & Reset -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">IPD Patient Intelligence</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Traceable Inpatient Admissions, Bed Utilization & Financials</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Basis Toggle -->
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <button type="button" wire:click="$set('dateBasis', 'admission')"
                            class="px-3 py-1.5 text-[10px] font-black uppercase rounded-lg transition-all {{ $dateBasis === 'admission' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Admission Date
                    </button>
                    <button type="button" wire:click="$set('dateBasis', 'discharge')"
                            class="px-3 py-1.5 text-[10px] font-black uppercase rounded-lg transition-all {{ $dateBasis === 'discharge' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Discharge Date
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
            <div class="lg:col-span-4">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Search Patient / IP No</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.350ms="search"
                           placeholder="Search name, UHID, phone, admission #..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Admission Status</label>
                <select wire:model.live="status" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Statuses</option>
                    <option value="Admitted">Admitted</option>
                    <option value="Discharged">Discharged</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Billing Status</label>
                <select wire:model.live="billingStatus" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Billing</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Partially Paid">Partially Paid</option>
                    <option value="Not Billed">Not Billed</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Ward</label>
                <select wire:model.live="wardId" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Wards</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Doctor</label>
                <select wire:model.live="doctorId" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Admissions</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ number_format($stats['summary']['total_admissions']) }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">In Period</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Currently Admitted</p>
            <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($stats['summary']['active_admissions']) }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Active Patients</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Discharges</p>
            <p class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ number_format($stats['summary']['discharges']) }}</p>
            <p class="text-[10px] font-bold text-amber-500 mt-1 uppercase">Discharged</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Billed</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($stats['summary']['total_invoiced'], 0) }}</p>
            <p class="text-[10px] font-bold text-blue-500 mt-1 uppercase">IP Invoiced</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Collections</p>
            <p class="text-2xl font-black text-emerald-600">₹{{ number_format($stats['summary']['total_collected'], 0) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1">Due: ₹{{ number_format($stats['summary']['total_due'], 0) }}</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Admission Velocity</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daily inpatient admission flow</p>
            </div>
            <x-chart type="bar" :data="$stats['daily_trend']" id="admission-trend-chart" label="Admissions" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Ward Distribution</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Admissions broken down by ward</p>
            </div>
            <x-chart type="doughnut" :data="$stats['ward_distribution']" id="ward-share-chart" label="Admissions" />
        </div>
    </div>

    <!-- Datatable -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Traceable Inpatient Records</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Click patient name or bill actions for complete history</p>
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
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Admission Info</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Ward / Bed</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Doctor</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Billing & Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Trace Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($admissions as $admission)
                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">#{{ $admission->admission_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $admission->days_admitted }} {{ Str::plural('Day', $admission->days_admitted) }}
                                </span>
                            </div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1">
                                Adm: {{ optional($admission->admission_date)->format('d M Y, h:i A') ?? 'N/A' }}
                            </div>
                            @if($admission->discharge_date)
                                <div class="text-[10px] font-semibold text-slate-400 mt-0.5">
                                    Dis: {{ $admission->discharge_date->format('d M Y, h:i A') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($admission->patient)
                                <a href="{{ route('counter.patients.history', $admission->patient->id) }}" 
                                   class="text-sm font-black text-indigo-600 hover:underline dark:text-indigo-400 block truncate">
                                    {{ $admission->patient->full_name }}
                                </a>
                                <div class="text-xs text-slate-500 font-mono mt-0.5">UHID: {{ $admission->patient->uhid ?? '—' }}</div>
                                @if($admission->patient->phone)
                                    <div class="text-[10px] font-semibold text-slate-400">Ph: {{ $admission->patient->phone }}</div>
                                @endif
                            @else
                                <span class="text-sm font-bold text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-black text-slate-800 dark:text-slate-200">{{ $admission->ward_name }}</div>
                            <div class="text-[10px] font-bold text-slate-400 mt-0.5">Bed: {{ optional($admission->bed)->bed_number ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ optional($admission->doctor)->full_name ?? 'N/A' }}</div>
                            <div class="text-[10px] font-semibold text-slate-400">{{ optional(optional($admission->doctor)->department)->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 mb-1">
                                @if($admission->status === 'Admitted')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">Admitted</span>
                                @elseif($admission->status === 'Discharged')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400">Discharged</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">{{ $admission->status }}</span>
                                @endif

                                @if($admission->finalBill)
                                    @if($admission->finalBill->payment_status === 'Paid')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">Paid</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">{{ $admission->finalBill->payment_status }}</span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Not Billed</span>
                                @endif
                            </div>

                            @if($admission->finalBill)
                                <div class="text-xs font-black text-slate-900 dark:text-white">
                                    ₹{{ number_format($admission->finalBill->total_amount, 2) }}
                                </div>
                                <div class="text-[10px] font-semibold text-slate-400">
                                    Paid: ₹{{ number_format($admission->finalBill->paid_amount, 2) }} · Due: ₹{{ number_format(max(0, $admission->finalBill->balance_amount), 2) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($admission->patient)
                                    <a href="{{ route('counter.patients.history', $admission->patient->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 text-xs font-bold transition-all" title="View Patient History">
                                        History
                                    </a>
                                @endif
                                
                                @if($admission->finalBill)
                                    <a href="{{ route('billing.bills.print', $admission->finalBill->id) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 hover:bg-indigo-100 text-xs font-bold transition-all">
                                        Bill
                                    </a>
                                @endif

                                @if($admission->status === 'Discharged')
                                    <a href="{{ route('discharge.summary', $admission->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 hover:bg-emerald-100 text-xs font-bold transition-all">
                                        Summary
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">No IPD admission records found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            {{ $admissions->links() }}
        </div>
    </div>
</div>

