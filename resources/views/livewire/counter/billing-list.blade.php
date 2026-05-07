<div>
    {{-- BILLING LIST --}}
    {{-- Stats Row --}}
    @if($activeTab === 'bills')
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="glass-card p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Today's Collection</p>
                <p class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($stats['today_revenue'], 2) }}</p>
            </div>
        </div>
        <div class="glass-card p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unpaid Bills</p>
                <p class="text-sm font-black text-gray-900 dark:text-white">{{ $stats['total_unpaid'] }}</p>
            </div>
        </div>
        <div class="glass-card p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Today OP</p>
                <p class="text-sm font-black text-gray-900 dark:text-white">{{ $stats['op_today'] }}</p>
            </div>
        </div>
        <div class="glass-card p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total OP</p>
                <p class="text-sm font-black text-gray-900 dark:text-white">{{ $stats['op_count'] }}</p>
            </div>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 mb-6">
        <div class="glass-card p-4 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total OP</p>
            <div class="flex items-end gap-2">
                <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $opStats['total'] }}</p>
                <span class="text-[10px] font-bold text-indigo-500 mb-1">Bookings</span>
            </div>
            <div class="mt-2 w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500 rounded-full" style="width: 100%"></div>
            </div>
        </div>
        <div class="glass-card p-4 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Review Visits</p>
            <div class="flex items-end gap-2">
                <p class="text-2xl font-black text-amber-600">{{ $opStats['review'] }}</p>
                <span class="text-[10px] font-bold text-amber-500 mb-1">Patients</span>
            </div>
            <div class="mt-2 w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $opStats['total'] > 0 ? ($opStats['review'] / $opStats['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="glass-card p-4 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Paid (Revenue)</p>
            <div class="flex items-end gap-2">
                <p class="text-2xl font-black text-emerald-600">{{ $opStats['paid'] }}</p>
                <span class="text-[10px] font-bold text-emerald-500 mb-1">Paid</span>
            </div>
            <div class="mt-2 w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $opStats['total'] > 0 ? ($opStats['paid'] / $opStats['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="glass-card p-4 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Amount</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white">₹{{ number_format($opStats['revenue'], 0) }}</p>
            <p class="text-[10px] font-bold text-blue-500 mt-1">Gross Collections</p>
        </div>
        <div class="glass-card p-4 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-rose-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Discounts</p>
            <p class="text-2xl font-black text-rose-600">₹{{ number_format($opStats['discount'], 0) }}</p>
            <p class="text-[10px] font-bold text-rose-500 mt-1">Total Reductions</p>
        </div>
    </div>
    @endif

    {{-- Tab Switcher --}}
    <div class="mb-4 border-b border-gray-100 dark:border-gray-800">
        <nav class="flex gap-1">
            <button wire:click="setTab('bills')" class="px-4 py-2 text-xs font-black uppercase tracking-widest border-b-2 {{ $activeTab === 'bills' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} transition-all">
                All Bills
            </button>
            <button wire:click="setTab('op')" class="px-4 py-2 text-xs font-black uppercase tracking-widest border-b-2 {{ $activeTab === 'op' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} transition-all">
                OP Bookings
            </button>
        </nav>
    </div>

    {{-- Filters --}}
    @if($activeTab === 'bills')
    <div class="glass-card p-4 mb-4">
        <div class="flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[200px]">
                <x-form.input
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search bill number, patient name or UHID…"
                    id="billing-search"
                />
            </div>
            <select wire:model.live="statusFilter"
                    id="billing-status-filter"
                    class="px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">All Statuses</option>
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
                <option value="Partially Paid">Partially Paid</option>
            </select>
            <select wire:model.live="methodFilter"
                    id="billing-method-filter"
                    class="px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">All Methods</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card / POS</option>
                <option value="UPI">UPI</option>
                <option value="Insurance">Insurance</option>
            </select>
            <input type="date" wire:model.live="dateFilter"
                   class="px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                   title="Filter by Date">
        </div>
    </div>
    @else
    <div class="glass-card p-3 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-3">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Search Patient</label>
                <x-form.input
                    wire:model.live.debounce.350ms="opSearch"
                    placeholder="Name, UHID, Phone..."
                    id="op-search"
                />
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Status</label>
                <select wire:model.live="opStatusFilter"
                        class="w-full px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Doctor</label>
                <select wire:model.live="opDoctorFilter"
                        class="w-full px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-4 flex items-center gap-2 bg-gray-100/50 dark:bg-gray-700/50 rounded-xl px-4 h-[42px]">
                <div class="flex-1 flex flex-col justify-center">
                    <span class="text-[9px] font-bold text-gray-400 uppercase leading-none mb-1">From</span>
                    <input type="date" wire:model.live="opFromDate" class="bg-transparent border-none text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-0 p-0 h-4">
                </div>
                <div class="w-px h-6 bg-gray-200 dark:bg-gray-600"></div>
                <div class="flex-1 flex flex-col justify-center text-right">
                    <span class="text-[9px] font-bold text-gray-400 uppercase leading-none mb-1">To</span>
                    <input type="date" wire:model.live="opToDate" class="bg-transparent border-none text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-0 p-0 h-4 text-right">
                </div>
            </div>
            <div class="lg:col-span-1 flex justify-end">
                <button wire:click="resetOpFilters" 
                        class="w-[42px] h-[42px] flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-rose-500 transition-all hover:rotate-90" title="Clear Filters">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="glass-card overflow-hidden">
        @if($activeTab === 'bills')
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($bills as $bill)
                    {{-- Mobile Bill Row --}}
                    <div wire:key="billing-mobile-{{ $bill->id }}" class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 truncate">{{ $bill->bill_number }}</p>
                                <p class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ $bill->patient->full_name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $bill->patient->uhid }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($bill->total_amount, 2) }}</p>
                                <p class="text-[10px] font-bold text-gray-400">Paid: ₹{{ number_format($bill->paid_amount, 2) }} · Due: ₹{{ number_format(max(0, $bill->balance_amount), 2) }}</p>
                                <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">{{ $bill->payment_method ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <div>
                                @if($bill->payment_status === 'Paid')
                                    <x-badge color="success">Paid</x-badge>
                                @elseif($bill->payment_status === 'Unpaid')
                                    <x-badge color="danger">Unpaid</x-badge>
                                @else
                                    <x-badge color="warning">{{ $bill->payment_status }}</x-badge>
                                @endif
                                <p class="text-xs text-gray-400 mt-2">{{ $bill->created_at->format('d M Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="openPaymentModal({{ $bill->id }})" class="btn btn-ghost px-3 py-2 text-xs">Collect</button>
                                <a href="{{ route('billing.bills.print', $bill->id) }}" target="_blank" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No bills found.</div>
                @endforelse
            </div>

            <div class="hidden md:block">
            <x-table.wrapper>
                <thead>
                    <tr>
                        <x-table.th>Bill No.</x-table.th>
                        <x-table.th>Patient</x-table.th>
                        <x-table.th class="text-right">Amount</x-table.th>
                        <x-table.th>Method</x-table.th>
                        <x-table.th>Status</x-table.th>
                        <x-table.th>Date</x-table.th>
                        <x-table.th class="text-right">Actions</x-table.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        <tr wire:key="billing-desktop-{{ $bill->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $bill->bill_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $bill->patient->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $bill->patient->uhid }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                ₹{{ number_format($bill->total_amount, 2) }}
                                <div class="text-[10px] font-bold text-gray-400 mt-1">
                                    Paid: ₹{{ number_format($bill->paid_amount, 2) }} · Due: ₹{{ number_format(max(0, $bill->balance_amount), 2) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $bill->payment_method ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($bill->payment_status === 'Paid') <x-badge color="success">Paid</x-badge>
                                @elseif($bill->payment_status === 'Unpaid') <x-badge color="danger">Unpaid</x-badge>
                                @else <x-badge color="warning">{{ $bill->payment_status }}</x-badge> @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $bill->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button wire:click="openPaymentModal({{ $bill->id }})" class="btn btn-ghost px-3 py-1.5 text-xs font-bold">Collect</button>
                                    <a href="{{ route('billing.bills.print', $bill->id) }}" target="_blank" class="btn btn-secondary px-3 py-1.5 text-xs font-bold">Print</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-table.empty colspan="7" message="No bills found." />
                    @endforelse
                </tbody>
            </x-table.wrapper>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700/50">{{ $bills->links() }}</div>
        @else
            {{-- OP BOOKINGS LIST --}}
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($ops as $op)
                    <div wire:key="op-mobile-{{ $op->id }}" class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 truncate">TOKEN #{{ $op->token_number }}</p>
                                <p class="font-black text-gray-900 dark:text-white text-sm truncate">{{ $op->patient->full_name }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-[10px] font-black text-rose-500 uppercase">M/O: {{ $op->patient->mother_name ?? '—' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-[10px] font-black text-emerald-600 uppercase">{{ $op->patient->address ?? '—' }}</span>
                                </div>
                                <p class="text-xs text-gray-400 truncate mt-1">{{ $op->patient->uhid }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($op->fee, 0) }}</p>
                                @if($op->discount_amount > 0)
                                    <p class="text-[10px] font-bold text-rose-500">Disc: ₹{{ number_format($op->discount_amount, 0) }}</p>
                                @endif
                                <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">{{ $op->payment_method ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($op->visit_type === 'Review')
                                    <x-badge color="amber">{{ $op->visit_type }}</x-badge>
                                @else
                                    <x-badge color="indigo">{{ $op->visit_type }}</x-badge>
                                @endif
                                
                                @if($op->bill)
                                    @if($op->bill->payment_status === 'Paid')
                                        <x-badge color="success">Paid</x-badge>
                                    @else
                                        <x-badge color="danger">{{ $op->bill->payment_status }}</x-badge>
                                    @endif
                                @else
                                    <x-badge color="warning">Not Billed</x-badge>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">{{ $op->consultation_date->format('d M Y') }}</p>
                        </div>
                                <div class="mt-3 flex gap-2">
                                    @unless($op->bill)
                                        <button wire:click="$dispatch('open-modal', { name: 'billing-create-modal' })" class="btn btn-primary px-3 py-2 text-xs flex-1">Create Bill</button>
                                    @else
                                        <x-badge color="indigo">Billed</x-badge>
                                    @endunless
                                    <button @click="$dispatch('print-op-slip', { id: {{ $op->id }} })" class="btn btn-secondary px-3 py-2 text-xs flex-1">Print Slip</button>
                                </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No OP bookings found.</div>
                @endforelse
            </div>

            <div class="hidden md:block">
            <x-table.wrapper>
                <thead>
                    <tr>
                        <x-table.th>Patient Info</x-table.th>
                        <x-table.th>Visit Details</x-table.th>
                        <x-table.th class="text-right">Pricing (₹)</x-table.th>
                        <x-table.th>Status</x-table.th>
                        <x-table.th class="text-right">Actions</x-table.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ops as $op)
                        <tr wire:key="op-desktop-{{ $op->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex flex-col items-center justify-center border border-indigo-100/50 dark:border-indigo-800/30 flex-shrink-0">
                                        <span class="text-[9px] font-black text-indigo-400 uppercase leading-none">Token</span>
                                        <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">#{{ $op->token_number }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-black text-gray-900 dark:text-white text-sm truncate">{{ $op->patient->full_name }}</p>
                                            <span class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/30 text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase">Child</span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="text-[10px] font-black text-rose-500 uppercase tracking-tight">M/O: {{ $op->patient->mother_name ?? '—' }}</span>
                                            <span class="text-gray-300">•</span>
                                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-tight">{{ $op->patient->address ?? '—' }}</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-tighter">{{ $op->patient->uhid }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    @if($op->visit_type === 'Review')
                                        <x-badge color="amber">{{ $op->visit_type }}</x-badge>
                                    @elseif($op->visit_type === 'Follow-up')
                                        <x-badge color="success">{{ $op->visit_type }}</x-badge>
                                    @else
                                        <x-badge color="indigo">{{ $op->visit_type }}</x-badge>
                                    @endif
                                    <span class="text-[10px] font-bold text-gray-400">{{ $op->consultation_date->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <p class="font-black text-gray-900 dark:text-white text-sm">₹{{ number_format($op->fee, 0) }}</p>
                                @if($op->discount_amount > 0)
                                    <p class="text-[10px] font-bold text-rose-500 mt-0.5">Discount: ₹{{ number_format($op->discount_amount, 0) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($op->bill)
                                    @if($op->bill->payment_status === 'Paid')
                                        <x-badge color="success">Paid</x-badge>
                                    @else
                                        <x-badge color="danger">{{ $op->bill->payment_status }}</x-badge>
                                    @endif
                                @else
                                    <x-badge color="warning">Not Billed</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                 <div class="flex justify-end gap-1">
                                    @unless($op->bill)
                                        <button wire:click="openOpDiscountModal({{ $op->id }})" class="btn btn-ghost px-3 py-1.5 text-xs font-bold text-rose-600">Discount</button>
                                        <button wire:click="$dispatch('open-modal', { name: 'billing-create-modal' })" class="btn btn-primary px-3 py-1.5 text-xs font-bold">Create Bill</button>
                                    @else
                                        <x-badge color="indigo">Billed</x-badge>
                                    @endunless
                                    <button @click="$dispatch('print-op-slip', { id: {{ $op->id }} })" class="btn btn-secondary px-3 py-1.5 text-xs font-bold" title="Print OPD Slip">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Print
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-table.empty colspan="5" message="No OP bookings found." />
                    @endforelse
                </tbody>
            </x-table.wrapper>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700/50">{{ $ops->links() }}</div>
        @endif
    </div>

    <x-modal name="billing-payment-modal" title="Collect Payment" width="xl">
        <div class="p-6 space-y-4">
            <x-form.select label="Type" wire:model="paymentType">
                <option value="payment">Payment</option>
                <option value="refund">Refund</option>
            </x-form.select>
            <x-form.select label="Method" wire:model="paymentMethod">
                <option value="Cash">Cash</option>
                <option value="Card">Card / POS</option>
                <option value="UPI">UPI</option>
                <option value="Insurance">Insurance</option>
            </x-form.select>
            <x-form.input type="number" step="1" label="Amount" wire:model.live.debounce.300ms="paymentAmount" class="text-right" />
            <x-form.input type="text" label="Reference (Optional)" wire:model="paymentReference" />
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest pl-1">Notes (Optional)</label>
                <textarea rows="2" class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 focus:bg-white dark:focus:bg-gray-800 transition-all duration-300 px-4 py-3 sm:text-sm resize-none" wire:model="paymentNotes"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="$dispatch('close-modal', { name: 'billing-payment-modal' })" class="btn btn-ghost px-6">Cancel</button>
                <button type="button" wire:click="submitPayment" wire:loading.attr="disabled" wire:target="submitPayment" class="btn btn-primary px-10">Save</button>
            </div>
        </div>
    </x-modal>

    <x-modal name="bill-discount-modal" title="Apply Authorized Discount" width="xl">
        <div class="p-6 space-y-4">
            <div class="bg-violet-50 dark:bg-violet-900/10 p-4 rounded-2xl border border-violet-100 dark:border-violet-800/30 mb-4">
                <p class="text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-widest mb-1">Clinical Authorization</p>
                @if($isAuthorizedByDoctor)
                    <p class="text-[11px] text-violet-500/80 leading-relaxed font-bold uppercase">
                        ✓ DOCTOR HAS AUTHORIZED STAFF TO APPLY DISCOUNT UP TO ₹{{ number_format($authorizedLimit, 2) }}.
                    </p>
                @else
                    <p class="text-[11px] text-rose-500/80 leading-relaxed font-bold uppercase">
                        ⚠ NO CLINICAL AUTHORIZATION GRANTED BY DOCTOR. ONLY DOCTORS OR ADMINS CAN PROCEED.
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form.select label="Discount Type" wire:model.live="discountType">
                    <option value="flat">Flat Amount (₹)</option>
                    <option value="percentage">Percentage (%)</option>
                </x-form.select>
                <x-form.input type="number" step="0.01" label="Value" wire:model.live="discountValue" class="text-right" />
            </div>

            @php
                $currentBill = \App\Models\Bill::find($selectedBillId);
            @endphp
            @if($currentBill && $currentBill->items->count() > 1)
                <x-form.select label="Apply to Specific Item (Optional)" wire:model.live="discountItemId">
                    <option value="">Full Bill (Grand Total)</option>
                    @foreach($currentBill->items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} (₹{{ number_format($item->total_price, 2) }})</option>
                    @endforeach
                </x-form.select>
            @endif

            <x-form.input type="text" label="Reason (Mandatory)" wire:model="discountReason" placeholder="e.g. Professional Courtesy, Staff Discount..." />

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="$dispatch('close-modal', { name: 'bill-discount-modal' })" class="btn btn-ghost px-6">Cancel</button>
                <button type="button" wire:click="submitDiscount" wire:loading.attr="disabled" wire:target="submitDiscount" class="btn btn-primary px-10" style="background-color: #7c3aed">Apply Discount</button>
            </div>
        </div>
    </x-modal>
    <x-modal name="op-discount-modal" title="OP Consultation Discount" width="xl">
        <div class="p-6 space-y-4">
            <div class="bg-amber-50 dark:bg-amber-900/10 p-4 rounded-2xl border border-amber-100 dark:border-amber-800/30 mb-4">
                <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest mb-1">Receptionist Discount</p>
                <p class="text-[11px] text-amber-500/80 leading-relaxed font-bold uppercase">
                    This will reduce the consultation fee and record the discount reason.
                </p>
            </div>

            <x-form.input type="number" step="0.01" label="Discount Amount (₹)" wire:model.live="opDiscountAmount" class="text-right" />
            
            <x-form.input type="text" label="Reason (Mandatory)" wire:model="opDiscountReason" placeholder="e.g. Doctor instructed, Professional Courtesy..." />

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="$dispatch('close-modal', { name: 'op-discount-modal' })" class="btn btn-ghost px-6">Cancel</button>
                <button type="button" wire:click="submitOpDiscount" wire:loading.attr="disabled" wire:target="submitOpDiscount" class="btn btn-primary px-10" style="background-color: #f59e0b">Apply Discount</button>
            </div>
        </div>
    </x-modal>
</div>
