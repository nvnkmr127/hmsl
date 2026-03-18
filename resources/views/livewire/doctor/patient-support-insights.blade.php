<div class="space-y-8 animate-in fade-in duration-500">
    {{-- E. Billing Summary --}}
    <div class="p-8 bg-white dark:bg-gray-950 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm">
        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6">Financials</h3>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[8px] font-black text-gray-400 uppercase mb-1">Today</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white">₹{{ $billStats['today_bill'] ? number_format($billStats['today_bill']->total_amount, 0) : '0' }}</p>
                </div>
                <span class="text-[10px] font-black px-3 py-1 bg-{{ $billStats['today_bill'] && $billStats['today_bill']->payment_status == 'Paid' ? 'emerald' : 'amber' }}-500 text-white rounded-full">
                    {{ $billStats['today_bill'] ? strtoupper($billStats['today_bill']->payment_status) : 'UNBILLED' }}
                </span>
            </div>
            <div class="pt-6 border-t border-gray-50 dark:border-gray-900">
                <p class="text-[8px] font-black text-gray-400 uppercase mb-1">30 Day Spend</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white">₹{{ number_format($billStats['thirty_days'], 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Insurance --}}
    <div class="p-8 bg-violet-50/50 dark:bg-violet-900/5 rounded-[2.5rem] border border-violet-100 dark:border-violet-900/30">
        <h3 class="text-[10px] font-black text-violet-600 uppercase tracking-[0.2em] mb-4">Insurance</h3>
        @if($insurance['provider'] ?? false)
            <p class="text-sm font-black text-gray-900 dark:text-white uppercase leading-none mb-1">{{ $insurance['provider'] }}</p>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $insurance['policy'] }}</p>
        @else
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No policy found</p>
        @endif
    </div>
</div>
