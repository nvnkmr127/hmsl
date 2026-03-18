<div>
    <x-page-header title="Doctors Directory" subtitle="Manage hospital medical staff, their specializations and consultation fees.">
        <x-slot name="actions">
            <button @click="$dispatch('create-doctor')" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Doctor
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <x-form.input placeholder="Search by name or specialization..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>
                <div class="md:col-span-2">
                    <x-form.select wire:model.live="departmentFilter" name="department_filter">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Doctor Info</x-table.th>
                    <x-table.th>Specialization</x-table.th>
                    <x-table.th>Fee</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($doctors as $doctor)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-sm"
                                     style="background:#7c3aed">
                                    {{ substr($doctor->full_name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $doctor->full_name }}</p>
                                    <p class="text-[10px] text-violet-600 dark:text-violet-400 font-bold uppercase tracking-widest truncate">{{ $doctor->department?->name ?? 'No Department' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $doctor->specialization }}</p>
                            <p class="text-[10px] text-gray-500 italic">{{ $doctor->qualification }}</p>
                        </td>
                        <td>
                            <span class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($doctor->consultation_fee) }}</span>
                        </td>
                        <td>
                            <x-badge :color="$doctor->is_active ? 'success' : 'danger'">
                                {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="toggleActive({{ $doctor->id }})" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Toggle Active">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </button>
                                <button @click="$dispatch('edit-doctor', { id: {{ $doctor->id }} })" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit Info">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteDoctor({{ $doctor->id }})" 
                                        wire:confirm="Remove this doctor from records?" 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="5" message="No doctors found matching filters..." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($doctors->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $doctors->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.doctor-form />
</div>
