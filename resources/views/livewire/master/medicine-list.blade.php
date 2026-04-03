<div>
    <x-page-header title="Pharmacy Inventory" subtitle="Track and manage medicines, stock levels, and expiry timelines.">
        <x-slot name="actions">
            <button wire:click="$dispatch('create-medicine')" class="btn btn-primary">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Medicine
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <x-form.input placeholder="Search by name or generic..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>
                <div>
                    <x-form.select wire:model.live="categoryFilter" name="category_filter">
                        <option value="">All Types</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="md:col-span-2 flex items-center px-2">
                    <x-form.checkbox label="Low Stock Alert Only" wire:model.live="lowStockOnly" name="low_stock_only" />
                </div>
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Medicine Info</x-table.th>
                    <x-table.th>Category</x-table.th>
                    <x-table.th>Stock Quantity</x-table.th>
                    <x-table.th>Selling Price</x-table.th>
                    <x-table.th>Expiry Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $med)
                    <tr>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $med->name }}</span>
                                <div class="flex items-center gap-2">
                                     <span class="text-tiny text-gray-400 font-mono font-black italic">{{ $med->code ?: 'NO CODE' }}</span>
                                     <span class="text-tiny text-violet-600 dark:text-violet-400 font-bold tracking-widest">{{ $med->generic_name ?: 'GENERIC NOT SET' }}</span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="flex flex-col">
                                <x-badge color="violet">{{ $med->category }}</x-badge>
                                <span class="text-tiny text-gray-500 font-bold mt-1 uppercase">{{ $med->strength }}</span>
                            </div>
                        </td>
                        <td>
                           <div class="flex items-center gap-2">
                                @php
                                    $isLow = $med->stock_quantity <= $med->min_stock_level;
                                @endphp
                                <span class="text-sm font-black {{ $isLow ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">
                                    {{ $med->stock_quantity }}
                                </span>
                                @if($isLow)
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span>
                                @endif
                           </div>
                        </td>
                        <td>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">₹{{ number_format($med->selling_price, 2) }}</span>
                        </td>
                        <td>
                            @php
                                $isExpired = $med->expire_date && $med->expire_date->isPast();
                                $isNearExpiry = $med->expire_date && !$isExpired && $med->expire_date->diffInMonths(now()) < 3;
                            @endphp
                            <span class="text-xs font-bold {{ $isExpired ? 'text-red-600' : ($isNearExpiry ? 'text-amber-500' : 'text-gray-600 dark:text-gray-400') }}">
                                {{ $med->expire_date ? $med->expire_date->format('d M, Y') : '—' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="$dispatch('edit-medicine', { id: {{ $med->id }} })" 

                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteMedicine({{ $med->id }})" 
                                        wire:confirm="Permanent delete this medicine record?" 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No medicines found in inventory matching criteria." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($medicines->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $medicines->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.medicine-form />
</div>
