<div>
    <x-page-header title="Pharmacy Stock" subtitle="Monitor medicine levels, track expiry, and record stock adjustments.">
        <x-slot name="actions">
            <a href="{{ route('pharmacy.index') }}" class="btn btn-secondary">Orders</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Filters">
                <div class="space-y-6">
                    <div>
                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block text-violet-600">Category Filter</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $categoryFilter === 'All' ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900 shadow-sm' : 'border-gray-100 dark:border-gray-800' }}">
                                <input type="radio" wire:model.live="categoryFilter" value="All" class="text-violet-600 focus:ring-violet-500">
                                <span class="text-xs font-bold uppercase tracking-widest {{ $categoryFilter === 'All' ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">All Types</span>
                            </label>
                            @foreach($categories as $cat)
                                <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $categoryFilter === $cat ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900 shadow-sm' : 'border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50' }}">
                                    <input type="radio" wire:model.live="categoryFilter" value="{{ $cat }}" class="text-violet-600 focus:ring-violet-500">
                                    <span class="text-xs font-bold uppercase tracking-widest {{ $categoryFilter === $cat ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">{{ $cat }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="showExpiredOnly" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-xs font-black uppercase tracking-widest text-red-600">Expired Only</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="showLowStockOnly" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-xs font-black uppercase tracking-widest text-amber-600">Low Stock Only</span>
                        </label>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
                    <x-form.input placeholder="Search medicines by name or generic..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>

                <div wire:loading.flex wire:target="search,categoryFilter,showExpiredOnly,showLowStockOnly" class="items-center justify-center p-6 text-xs font-black uppercase tracking-widest text-gray-400">
                    Syncing live inventory data...
                </div>

                <x-table.wrapper wire:loading.remove wire:target="search,categoryFilter,showExpiredOnly,showLowStockOnly">
                    <thead>
                        <tr>
                            <x-table.th>Medicine</x-table.th>
                            <x-table.th class="hidden md:table-cell">Batch / Expire</x-table.th>
                            <x-table.th>Quantity</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicines as $m)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-sm bg-violet-600">
                                            {{ strtoupper(substr($m->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 dark:text-white truncate uppercase tracking-tight">{{ $m->name }}</p>
                                            <p class="text-[10px] text-violet-600 dark:text-violet-400 font-bold uppercase tracking-widest truncate">{{ $m->category }} | {{ $m->strength }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $m->generic_name ?? '—' }}</p>
                                    @php
                                        $isExpired = $m->expire_date && $m->expire_date->isPast();
                                        $isNearExpiry = $m->expire_date && !$isExpired && $m->expire_date->diffInMonths(now()) < 3;
                                    @endphp
                                    <p class="text-[10px] uppercase font-black tracking-widest {{ $isExpired ? 'text-red-500' : ($isNearExpiry ? 'text-amber-500' : 'text-gray-400') }}">
                                        Exp: {{ $m->expire_date ? $m->expire_date->format('d M, Y') : 'N/A' }}
                                    </p>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @php $low = $m->stock_quantity <= $m->min_stock_level @endphp
                                        <span class="text-sm font-black {{ $low ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                            {{ $m->stock_quantity }}
                                        </span>
                                        @if($low)
                                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="selectForStockAdjustment({{ $m->id }})" 
                                                wire:loading.attr="disabled" wire:target="selectForStockAdjustment({{ $m->id }})"
                                                class="btn btn-primary px-3 py-1.5 text-xs">
                                            Adjust
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="4" message="No medicines found in stock matching criteria." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($medicines->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $medicines->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <x-modal name="stock-adjustment-modal" title="Record stock Adjustment">
        <form wire:submit.prevent="submitAdjustment">
            <div class="space-y-6">
                @if($selectedMedicineId)
                    @php $med = \App\Models\Medicine::find($selectedMedicineId); @endphp
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Adjusting Stock For</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $med?->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Current Stock</p>
                            <p class="text-sm font-black text-violet-600 mt-1">{{ $med?->stock_quantity }}</p>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Quantity Change (use negative for deduction)</label>
                    <x-form.input type="number" wire:model="adjustmentQuantity" placeholder="e.g., 50 or -10" />
                </div>

                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Reason / Movement Details</label>
                    <x-form.textarea wire:model="adjustmentNotes" placeholder="Received new batch from supplier, damage replacement, etc." rows="3" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'stock-adjustment-modal' })" class="btn btn-ghost">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="submitAdjustment" class="btn btn-primary">
                    <span wire:loading.remove wire:target="submitAdjustment">Update Live Stock</span>
                    <span wire:loading wire:target="submitAdjustment">Updating...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
