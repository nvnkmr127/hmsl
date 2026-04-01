<div>
    <x-page-header title="Inventory Control" subtitle="Monitor supply levels, track stock movements, and manage item categories.">
        <x-slot name="actions">
            <a href="{{ route('inventory.stock') }}" class="btn btn-secondary">Stock</a>
            <a href="{{ route('inventory.suppliers') }}" class="btn btn-secondary">Suppliers</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Categories">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $categoryFilter === 'All' ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900 shadow-sm' : 'border-gray-100 dark:border-gray-800' }}">
                            <input type="radio" wire:model.live="categoryFilter" value="All" class="text-violet-600 focus:ring-violet-500">
                            <span class="text-xs font-bold uppercase tracking-widest {{ $categoryFilter === 'All' ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">All Supplies</span>
                        </label>
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ (string)$categoryFilter === (string)$cat->id ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900 shadow-sm' : 'border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50' }}">
                                <input type="radio" wire:model.live="categoryFilter" value="{{ $cat->id }}" class="text-violet-600 focus:ring-violet-500">
                                <span class="text-xs font-bold uppercase tracking-widest {{ (string)$categoryFilter === (string)$cat->id ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
                    <x-form.input placeholder="Search items by name or SKU..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>

                <div wire:loading.flex wire:target="search,categoryFilter,selectForStockAdjustment,submitAdjustment" class="items-center justify-center p-6 text-xs font-black uppercase tracking-widest text-gray-400">
                    Loading inventory...
                </div>

                <x-table.wrapper wire:loading.remove wire:target="search,categoryFilter,selectForStockAdjustment,submitAdjustment">
                    <thead>
                        <tr>
                            <x-table.th>Item</x-table.th>
                            <x-table.th class="hidden md:table-cell">Category</x-table.th>
                            <x-table.th>In Stock</x-table.th>
                            <x-table.th class="hidden lg:table-cell">Min Level</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-sm bg-violet-600">
                                            {{ strtoupper(substr($i->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 dark:text-white truncate">{{ $i->name }}</p>
                                            <p class="text-[10px] text-violet-600 dark:text-violet-400 font-bold uppercase tracking-widest truncate">{{ $i->sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $i->category->name }}</p>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($i->stock_quantity <= $i->min_stock_level)
                                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        @endif
                                        <span class="text-sm font-black {{ $i->stock_quantity <= $i->min_stock_level ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                            {{ $i->stock_quantity }} {{ $i->unit }}
                                        </span>
                                    </div>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <span class="text-xs text-gray-500 font-medium">{{ $i->min_stock_level }} {{ $i->unit }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="selectForStockAdjustment({{ $i->id }})" 
                                                wire:loading.attr="disabled" wire:target="selectForStockAdjustment({{ $i->id }})"
                                                class="btn btn-primary px-3 py-1.5 text-xs">
                                            <span wire:loading.remove wire:target="selectForStockAdjustment({{ $i->id }})">Adjust Stock</span>
                                            <span wire:loading wire:target="selectForStockAdjustment({{ $i->id }})">Opening...</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No inventory items found matching your filters..." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($items->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $items->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <x-modal name="stock-adjustment-modal" title="Record Stock Adjustment">
        <form wire:submit.prevent="submitAdjustment">
            <div class="space-y-6">
                <p class="text-xs text-gray-500 leading-relaxed italic">Manually update stock levels for purchases, consumption, or breakage. All adjustments are logged for audit purposes.</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Type</label>
                        <x-form.select wire:model="adjustmentType" name="adj_type">
                            <option value="in">Inflow / Purchase (+)</option>
                            <option value="out">Outflow / Usage (-)</option>
                            <option value="correction">Correction (=)</option>
                        </x-form.select>
                    </div>
                    <div>
                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Quantity</label>
                        <x-form.input type="number" wire:model="adjustmentQuantity" placeholder="0" />
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Adjustment Reason / Notes</label>
                    <x-form.textarea wire:model="adjustmentNotes" placeholder="e.g., Damaged item replacement, periodic stock-take..." rows="3" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'stock-adjustment-modal' })" class="btn btn-ghost">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="submitAdjustment" class="btn btn-primary">
                    <span wire:loading.remove wire:target="submitAdjustment">Save Adjustment</span>
                    <span wire:loading wire:target="submitAdjustment">Saving...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
