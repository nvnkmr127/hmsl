<div>
    <x-page-header title="Inventory Suppliers" subtitle="Manage supply partners and contact details for medical inventory.">
        <x-slot name="actions">
            <button wire:click="openCreate" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Supplier
            </button>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Search & Quick Info">
                <div class="space-y-4">
                    <x-form.input placeholder="Search suppliers..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    <p class="text-[10px] text-gray-400 font-medium italic">Active suppliers are eligible for purchase orders and inventory transactions.</p>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Supplier Info</x-table.th>
                            <x-table.th class="hidden md:table-cell">Contact Person</x-table.th>
                            <x-table.th class="hidden lg:table-cell">Contact Phone</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $s)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-sm bg-violet-600">
                                            {{ strtoupper(substr($s->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $s->name }}</p>
                                            <p class="text-[10px] text-violet-600 font-bold uppercase tracking-widest">{{ $s->email ?? 'NO EMAIL' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $s->contact_person }}</p>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $s->phone }}</p>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $s->id }})" class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button wire:click="delete({{ $s->id }})" wire:confirm="Remove this supplier? History records may prevent deletion." class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="4" message="No suppliers found..." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($suppliers->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $suppliers->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Supplier Modal -->
    <x-modal name="supplier-modal" title="{{ $selectedSupplierId ? 'Edit Supplier' : 'New Supplier' }}">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block text-violet-600">Company / Supplier Name</label>
                    <x-form.input wire:model="name" placeholder="Acme Medical Supplies Ltd." />
                </div>
                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Contact Person</label>
                    <x-form.input wire:model="contact_person" placeholder="John Doe" />
                </div>
                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Phone</label>
                    <x-form.input wire:model="phone" placeholder="+91 9988776655" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Email</label>
                    <x-form.input type="email" wire:model="email" placeholder="contact@acme.com" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Address</label>
                    <x-form.textarea wire:model="address" placeholder="Postal address details..." rows="3" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'supplier-modal' })" class="btn btn-ghost">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                    <span wire:loading.remove wire:target="save">Save Supplier</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
