<div>
    <x-page-header title="Test Definitions" subtitle="Configure available lab tests, reference ranges, and pricing.">
        <x-slot name="actions">
            <button wire:click="openCreate" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add New Test
            </button>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Filters">
                <div class="space-y-4">
                    <x-form.input placeholder="Search test name..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    <div>
                         <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Category</label>
                         <x-form.select wire:model.live="categoryFilter">
                            <option value="All">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                         </x-form.select>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Test Name</x-table.th>
                            <x-table.th class="hidden md:table-cell">Category</x-table.th>
                            <x-table.th>Price</x-table.th>
                            <x-table.th class="hidden lg:table-cell">Parameters</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tests as $t)
                            <tr>
                                <td>
                                    <p class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $t->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium italic">{{ $t->description ?: 'No description' }}</p>
                                </td>
                                <td class="hidden md:table-cell">
                                    <x-badge color="violet">{{ $t->category ?? 'General' }}</x-badge>
                                </td>
                                <td>
                                    <span class="text-sm font-black text-gray-900 dark:text-white tracking-tight">₹{{ number_format($t->price, 2) }}</span>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <p class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ $t->parameters->count() }} Parameters</p>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $t->id }})" class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No lab tests configured yet." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($tests->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $tests->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Test Modal -->
    <x-modal name="test-modal" title="{{ $selectedTestId ? 'Edit Lab Test' : 'New Lab Test' }}" maxWidth="4xl">
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block text-violet-600">Test Name</label>
                    <x-form.input wire:model="testName" placeholder="e.g. Complete Blood Count (CBC)" />
                </div>
                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Category</label>
                    <x-form.input wire:model="testCategory" placeholder="Haematology" />
                </div>
                <div>
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Base Price (INR)</label>
                    <x-form.input type="number" wire:model="testPrice" placeholder="500" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Description / Notes</label>
                    <x-form.input wire:model="testDescription" placeholder="Optional notes for report layout..." />
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800 pt-8">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-[0.2em]">Test Parameters & Reference Ranges</h4>
                    <button type="button" wire:click="addParameter" class="btn btn-secondary px-4 py-2 text-[10px] font-black uppercase tracking-widest">
                        Add Parameter
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($parameters as $index => $p)
                        <div class="flex items-center gap-3 animate-in slide-in-from-top-2 duration-200">
                            <div class="flex-1 grid grid-cols-3 gap-3">
                                <x-form.input wire:model="parameters.{{ $index }}.name" placeholder="Parameter Name (e.g. WBC)" />
                                <x-form.input wire:model="parameters.{{ $index }}.unit" placeholder="Unit (e.g. cells/uL)" />
                                <x-form.input wire:model="parameters.{{ $index }}.reference_range" placeholder="Range (e.g. 4000-11000)" />
                            </div>
                            <button type="button" wire:click="removeParameter({{ $index }})" class="p-2 text-red-500 hover:bg-red-500/10 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    @endforeach
                    @if(empty($parameters))
                        <p class="text-[10px] text-center text-gray-400 font-bold uppercase py-6 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-[2rem]">No parameters defined. Add at least one to enable result entry.</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'test-modal' })" class="btn btn-ghost">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                    <span wire:loading.remove wire:target="save">Save Test Profile</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
