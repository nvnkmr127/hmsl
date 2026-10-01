<div class="space-y-8">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 lg:p-8 shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
        <!-- Top Row: Title, Date Range, Search & Reset -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                @if(!$isDashboard)
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Executive Discount Governance & Audit Trail</h1>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">Traceable Concession Authorizations, Financial Governance, Specialty Attribution & Approval Workflows</p>
                @else
                    <h2 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Discount Approvals</h2>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Range -->
                <div class="flex items-center gap-2 bg-slate-100/70 dark:bg-slate-800/70 px-3 h-[42px] rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase">From</span>
                        <input type="date" wire:model.live="fromDate" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 h-4">
                    </div>
                    <span class="text-slate-500 dark:text-slate-300">•</span>
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase">To</span>
                        <input type="date" wire:model.live="toDate" class="bg-transparent border-none text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 p-0 h-4">
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
            <div class="lg:col-span-4">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Search Bill / Patient / Reason</label>
                <input type="text" wire:model.live.debounce.350ms="search"
                       placeholder="Search bill #, patient name, reason..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Approval Status</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Statuses</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending Approval</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Department</label>
                <select wire:model.live="departmentId" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Doctor / Clinical Auth</label>
                <select wire:model.live="doctorId" class="w-full px-3 py-2 rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Discounts</p>
            <p class="text-3xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($stats['summary']['total_discount_amount'], 2) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">Cumulative Concessions</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Approved Discounts</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($stats['summary']['approved_amount'], 2) }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Authorized Waivers</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Pending Approvals</p>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400">₹{{ number_format($stats['summary']['pending_amount'], 2) }}</p>
            <p class="text-[10px] font-bold text-amber-500 mt-1 uppercase">Awaiting Authorization</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Discount Instances</p>
            <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($stats['summary']['discounts_count']) }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">Total Concessions</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Approval Rate</p>
            <p class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ $stats['summary']['approval_rate'] }}%</p>
            <p class="text-[10px] font-bold text-purple-500 mt-1 uppercase">Governance Compliance</p>
        </div>
    </div>

    <!-- Advanced Operational Audit Insights -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Highest Single Discount</p>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400">₹{{ number_format($stats['summary']['highest_amount'], 2) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1 uppercase">Bill #{{ $stats['summary']['highest_bill'] }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Top Authorizing Authority</p>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 truncate">{{ $stats['summary']['top_authorizer'] }}</p>
            <p class="text-[10px] font-bold text-indigo-500 mt-1 uppercase">Most Frequent Approver</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Highest Concession Specialty</p>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 truncate">{{ $stats['summary']['top_department'] }}</p>
            <p class="text-[10px] font-bold text-emerald-500 mt-1 uppercase">Maximum discounts by department</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-all">
            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Audit Governance</p>
            <p class="text-2xl font-black text-purple-600 dark:text-purple-400">ACTIVE</p>
            <p class="text-[10px] font-bold text-purple-500 mt-1 uppercase">100% Traceable Logs</p>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Daily Discount Trend</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Concession volume trajectory over time</p>
            </div>
            <x-chart type="line" :data="$stats['daily_trend']" id="discount-trend-chart" label="Discounts (₹)" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Discount Share by Status</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Proportion of approved vs pending vs rejected</p>
            </div>
            <x-chart type="doughnut" :data="$stats['status_share']" id="status-share-chart" label="Amount (₹)" />
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Department-Wise Concessions</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Discounts attributed by clinical specialty</p>
            </div>
            <x-chart type="bar" :data="$stats['department_wise']" id="dept-discount-chart" label="Discounts (₹)" />
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Doctor-Wise Concession Attribution</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total discount volume by attending doctor</p>
            </div>
            <x-chart type="bar" :data="$stats['doctor_wise']" id="doctor-discount-chart" label="Discounts (₹)" />
        </div>
    </div>

    <!-- Datatable -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Traceable Discount Audit Logs</h3>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-0.5">Verify transparency, justification and authorizer identity</p>
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
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Date & Time</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Bill & Patient Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Applied By / Doctor</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Discount Value</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Justification Reason</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status & Governance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($discounts as $discount)
                    <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $discount->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $discount->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs font-black text-indigo-600 dark:text-indigo-400 mb-0.5">
                                {{ $discount->bill->bill_number ?? '—' }}
                            </div>
                            @if($discount->bill && $discount->bill->patient)
                                <a href="{{ route('counter.patients.history', $discount->bill->patient_id) }}" 
                                   class="text-xs font-black text-slate-800 hover:text-indigo-600 dark:text-slate-200 dark:hover:text-indigo-400 block truncate">
                                    {{ $discount->bill->patient->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-600 dark:text-slate-400 font-mono mt-0.5">UHID: {{ $discount->bill->patient->uhid ?? '—' }}</div>
                            @else
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[9px] font-black text-slate-600 dark:text-slate-400">
                                    {{ strtoupper(substr($discount->appliedBy->name ?? '?', 0, 2)) }}
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $discount->appliedBy->name ?? 'Unknown Staff' }}</span>
                            </div>
                            @if($discount->approver)
                                <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">Approved by: {{ $discount->approver->name }}</div>
                            @elseif($discount->doctor)
                                <div class="text-[10px] font-black text-purple-600 dark:text-purple-400">Auth Doctor: {{ $discount->doctor->full_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-xs font-black text-rose-600 dark:text-rose-400">- ₹{{ number_format($discount->applied_amount, 2) }}</div>
                            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mt-0.5">
                                ({{ $discount->discount_type === 'percentage' ? $discount->discount_value . '%' : 'Flat' }})
                            </div>
                            @if($discount->bill)
                                <div class="text-[9px] text-slate-500 dark:text-slate-400">Bill Total: ₹{{ number_format($discount->bill->total_amount, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 italic max-w-xs truncate" title="{{ $discount->reason }}">
                                "{{ $discount->reason ?? 'No reason provided' }}"
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            @if($discount->status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">Approved</span>
                            @elseif($discount->status === 'pending')
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400">Pending</span>
                                    @if(Auth::user() && (Auth::user()->hasAnyRole(['admin', 'super_admin']) || \App\Models\Doctor::where('user_id', Auth::id())->exists()))
                                        <button wire:click="approve({{ $discount->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-all" title="Approve">
                                            <span>Approve</span>
                                        </button>
                                        <button wire:click="reject({{ $discount->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition-all" title="Reject">
                                            <span>Reject</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">Rejected</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs font-bold uppercase tracking-widest">No Discount Records Found</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($discounts->hasPages())
        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            {{ $discounts->links() }}
        </div>
        @endif
    </div>
</div>
