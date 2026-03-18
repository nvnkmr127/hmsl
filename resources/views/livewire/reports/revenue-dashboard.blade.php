<div class="space-y-8">
    <!-- Filter Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Revenue Analytics</h2>
            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-1">Financial performance across departments</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-form.select wire:model.live="dateRange" class="w-48">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="custom">Custom Range</option>
            </x-form.select>
            @if($dateRange === 'custom')
                <div class="flex items-center space-x-2">
                    <input type="date" wire:model.live="startDate" class="bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-xs font-bold px-4 py-2 opacity-70">
                    <span class="text-gray-400 font-bold">to</span>
                    <input type="date" wire:model.live="endDate" class="bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-xs font-bold px-4 py-2 opacity-70">
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-10 transition-transform group-hover:scale-110">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 mb-2">Total Collection</p>
            <h3 class="text-3xl font-black italic">₹{{ number_format($totalRevenue, 2) }}</h3>
            <p class="text-[10px] font-bold mt-4 text-indigo-100">{{ $totalBills }} SETTLED INVOICES</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm relative overflow-hidden group">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Net Discount</p>
            <h3 class="text-3xl font-black text-rose-500 italic">₹{{ number_format($totalDiscount, 2) }}</h3>
            <div class="mt-4 w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                <div class="bg-rose-500 h-full" style="width: {{ $totalRevenue > 0 ? ($totalDiscount / ($totalRevenue + $totalDiscount)) * 100 : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm relative overflow-hidden group">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Tax Collected</p>
            <h3 class="text-3xl font-black text-emerald-500 italic">₹{{ number_format($totalTax, 2) }}</h3>
            <p class="text-[10px] font-bold mt-4 text-gray-400 uppercase tracking-widest">Calculated GST/VAT</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm relative overflow-hidden group">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Avg. Ticket Size</p>
            <h3 class="text-3xl font-black text-gray-800 dark:text-white italic">₹{{ $totalBills > 0 ? number_format($totalRevenue / $totalBills, 2) : '0.00' }}</h3>
            <p class="text-[10px] font-bold mt-4 text-gray-400 uppercase tracking-widest">per patient visit</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue Splits -->
        <div class="lg:col-span-1 space-y-8">
            <x-card title="Department Split">
                <div class="space-y-4">
                    @forelse($departmentSplit as $dept)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span class="text-xs font-black text-gray-600 dark:text-gray-400 uppercase tracking-tight">{{ $dept['item_type'] }}</span>
                            </div>
                            <span class="text-sm font-black text-gray-800 dark:text-gray-200">₹{{ number_format($dept['total'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-center py-4 text-xs text-gray-400 italic">No departmental data.</p>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Payment Methods">
                <div class="grid grid-cols-2 gap-4">
                    @forelse($paymentMethodSplit as $method)
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-3xl border border-gray-100 dark:border-gray-700/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $method['payment_method'] ?: 'Other' }}</p>
                            <p class="text-lg font-black text-gray-800 dark:text-gray-100">₹{{ number_format($method['total'] / 1000, 1) }}k</p>
                        </div>
                    @empty
                        <p class="col-span-2 text-center py-4 text-xs text-gray-400 italic">No payment data.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-2">
            <x-card title="Recent Settlements" subtitle="Latest invoices processed in this period.">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Bill Details</x-table.th>
                            <x-table.th>Patient</x-table.th>
                            <x-table.th>Amount</x-table.th>
                            <x-table.th>Method</x-table.th>
                            <x-table.th class="text-right">Time</x-table.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($recentBills as $bill)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-indigo-600 text-sm italic">{{ $bill->bill_number }}</span>
                                </td>
                                <td class="px-6 py-4 lowercase first-letter:uppercase">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase">{{ $bill->patient->full_name }}</span>
                                        <span class="text-[10px] text-gray-400 font-bold tracking-widest">{{ $bill->patient->uhid }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-gray-800 dark:text-gray-200">₹{{ number_format($bill->total_amount, 2) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge color="sky">{{ $bill->payment_method }}</x-badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $bill->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No settlements found for this period." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </x-card>
        </div>
    </div>
</div>
