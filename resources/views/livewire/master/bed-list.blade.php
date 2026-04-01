<div>
    <x-page-header title="Standard Bed Matrix" subtitle="Monitor and manage hospital bed infrastructure across wards and specialized units.">
        <x-slot name="actions">
            <button wire:click="$dispatch('create-bed')" class="btn btn-primary">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Bed
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <x-form.input placeholder="Search bed numbers..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>
                <div class="md:col-span-2">
                    <x-form.select wire:model.live="wardFilter" name="ward_filter">
                        <option value="">All Wards</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Bed No.</x-table.th>
                    <x-table.th>Ward Assignment</x-table.th>
                    <x-table.th>Availability</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($beds as $bed)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-colors">
                        <td>
                            <span class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tighter">{{ $bed->bed_number }}</span>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 dark:text-white uppercase">{{ $bed->ward->name }}</span>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $bed->ward->type }}</span>
                            </div>
                        </td>
                        <td>
                            <x-badge :color="$bed->is_available ? 'success' : 'danger'">
                                {{ $bed->is_available ? 'FREE' : 'OCCUPIED' }}
                            </x-badge>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="toggleAvailability({{ $bed->id }})" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Toggle Status">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.14l.539-.307L2 15l4.161-4.041.539.307c1.744-2.772 2.753-6.054 2.753-9.571V3h4v.26c0 3.517 1.009 6.799 2.753 9.571l.539-.307L22 15l-4.161 4.041-.539-.307C15.553 15.966 14.544 12.684 14.544 9.167V9h-4v.167c0 3.517-1.009 6.799-2.753 9.571l.539.307z" />
                                    </svg>
                                </button>
                                <button wire:click="$dispatch('edit-bed', { id: {{ $bed->id }} })" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit Configuration">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteBed({{ $bed->id }})" 
                                        wire:confirm="Permanent decommissioning of this bed?" 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="4" message="No beds have been registered in the system." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($beds->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $beds->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.bed-form />
</div>
