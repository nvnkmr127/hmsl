<div>
    <x-page-header title="Departments" subtitle="Manage hospital departments like OPD, ICU, Radiology, etc.">
        <x-slot name="actions">
            <button wire:click="$dispatch('create-department')" class="btn btn-primary">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Department
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="max-w-md">
                <x-form.input placeholder="Search departments..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Code</x-table.th>
                    <x-table.th>Name</x-table.th>
                    <x-table.th>Description</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td>
                            <span class="text-tiny font-black font-mono text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-lg">
                                {{ $dept->code ?: 'N/A' }}
                            </span>
                        </td>

                        <td>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $dept->name }}</span>
                        </td>
                        <td>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 line-clamp-1">{{ $dept->description ?: 'No description provided.' }}</p>
                        </td>
                        <td>
                            <x-badge :color="$dept->is_active ? 'success' : 'danger'">
                                {{ $dept->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="toggleActive({{ $dept->id }})" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Toggle Active">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </button>
                                <button wire:click="$dispatch('edit-department', { id: {{ $dept->id }} })" 

                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteDepartment({{ $dept->id }})" 
                                        wire:confirm="Permanent delete this department?" 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="4" message="No departments found..." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($departments->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $departments->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.department-form />
</div>
