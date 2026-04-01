<div>
    {{-- BILLING LIST --}}
    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Today's Revenue</p>
                <p class="text-xl font-black text-gray-900 dark:text-white">₹{{ number_format($stats['today_revenue'], 2) }}</p>
            </div>
        </div>
        <div class="glass-card p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Collected</p>
                <p class="text-xl font-black text-gray-900 dark:text-white">₹{{ number_format($stats['total_paid'], 2) }}</p>
            </div>
        </div>
        <div class="glass-card p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Unpaid Bills</p>
                <p class="text-xl font-black text-gray-900 dark:text-white">{{ $stats['total_unpaid'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
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
        </div>
    </div>

    {{-- Table --}}
    <div class="glass-card overflow-hidden">
        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($bills as $bill)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 truncate">{{ $bill->bill_number }}</p>
                            <p class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ $bill->patient->full_name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $bill->patient->uhid }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($bill->total_amount, 2) }}</p>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mt-1">{{ $bill->payment_method ?? '—' }}</p>
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
                            <a href="{{ route('billing.bills.print', $bill->id) }}"
                               target="_blank"
                               class="btn btn-secondary px-3 py-2 text-xs">
                                Print
                            </a>
                            <button
                               wire:click="sendEmail({{ $bill->id }})"
                               wire:loading.attr="disabled"
                               class="btn btn-ghost px-3 py-2 text-xs">
                                <span wire:loading.remove wire:target="sendEmail({{ $bill->id }})">Email</span>
                                <span wire:loading wire:target="sendEmail({{ $bill->id }})">...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                    No bills found.
                </div>
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
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $bill->bill_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $bill->patient->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $bill->patient->uhid }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                            ₹{{ number_format($bill->total_amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ $bill->payment_method ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($bill->payment_status === 'Paid')
                                <x-badge color="success">Paid</x-badge>
                            @elseif($bill->payment_status === 'Unpaid')
                                <x-badge color="danger">Unpaid</x-badge>
                            @else
                                <x-badge color="warning">{{ $bill->payment_status }}</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            {{ $bill->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('billing.bills.print', $bill->id) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200 transition-colors px-3 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                    Print
                                </a>
                                <button
                                   wire:click="sendEmail({{ $bill->id }})"
                                   wire:loading.attr="disabled"
                                   class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200 transition-colors px-3 py-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                                    <span wire:loading.remove wire:target="sendEmail({{ $bill->id }})">Email</span>
                                    <span wire:loading wire:target="sendEmail({{ $bill->id }})">...</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colspan="8" message="No bills found. Generate a bill from OPD Bookings." />
                @endforelse
            </tbody>
        </x-table.wrapper>
        </div>

        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700/50">
            {{ $bills->links() }}
        </div>
    </div>
</div>
