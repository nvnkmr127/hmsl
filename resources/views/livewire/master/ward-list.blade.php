<div>
    <x-page-header title="Hospital Wards & Beds" subtitle="Manage inpatient facilities, assignment types, and monitor real-time bed availability.">
        <x-slot name="actions">
            <button wire:click="$dispatch('create-ward')" class="btn btn-primary">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Ward
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="max-w-md">
                <x-form.input placeholder="Search by name or facility type..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Code</x-table.th>
                    <x-table.th>Ward Name / Wing</x-table.th>
                    <x-table.th>Facility Type</x-table.th>
                    <x-table.th>Capacity</x-table.th>
                    <x-table.th>Daily Fee</x-table.th>
                    <x-table.th>Live Occupancy</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($wards as $ward)
                    <tr>
                        <td>
                            <span class="text-[10px] font-black font-mono text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-lg">
                                {{ $ward->code ?: 'N/A' }}
                            </span>
                        </td>

                        <td>
                            <span class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $ward->name }}</span>
                        </td>
                        <td>
                            <x-badge color="violet">{{ $ward->type }}</x-badge>
                        </td>
                        <td>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $ward->capacity }} UNIT(S)</span>
                        </td>
                        <td>
                            <span class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($ward->daily_charge, 2) }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    @php 
                                        $percent = $ward->beds_count > 0 ? (($ward->beds_count - $ward->available_beds_count) / $ward->beds_count) * 100 : 0;
                                    @endphp
                                    <div class="h-full bg-violet-600 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">
                                    {{ $ward->beds_count - $ward->available_beds_count }} / {{ $ward->beds_count }} OCCUPIED
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="$dispatch('edit-ward', { id: {{ $ward->id }} })" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteWard({{ $ward->id }})" 
                                        wire:confirm="Permanent delete this ward and its associated beds? This cannot be undone." 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No hospital wards are currently registered in the system." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($wards->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $wards->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.ward-form />
</div>
