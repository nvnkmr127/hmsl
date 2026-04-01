<div>
    <x-page-header
        title="Patient Registry"
        subtitle="View and manage all registered patients in the system.">
        <x-slot name="actions">
            <button @click="$dispatch('create-patient')" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Register Patient
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="max-w-md">
                <x-form.input
                    placeholder="Search by name, UHID, or phone..."
                    wire:model.live.debounce.300ms="search"
                    icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </div>
        </div>

        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($patients as $patient)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $patient->uhid }}</p>
                            <p class="text-sm font-black text-gray-900 dark:text-white truncate mt-1">{{ $patient->full_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $patient->gender }} · {{ $patient->age }} Years</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $patient->phone }}</p>
                        </div>
                        <div class="flex-shrink-0 flex flex-col gap-2">
                            <a href="{{ route('counter.patients.history', $patient->id) }}" class="btn btn-secondary px-3 py-2 text-xs">History</a>
                            @can('view opd')
                            <a href="{{ route('counter.opd.index', ['patient_id' => $patient->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">OP Token</a>
                            @endcan
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button @click="$dispatch('edit-patient', { id: {{ $patient->id }} })" class="btn btn-ghost px-3 py-2 text-xs">Edit</button>
                        <button wire:click="deletePatient({{ $patient->id }})"
                                wire:confirm="Are you sure you want to delete this patient record?"
                                class="btn btn-ghost px-3 py-2 text-xs text-red-500">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No patients found.</div>
            @endforelse
        </div>

        <div class="hidden md:block">
        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>UHID</x-table.th>
                    <x-table.th>Patient Name</x-table.th>
                    <x-table.th>Gender / Age</x-table.th>
                    <x-table.th>Phone</x-table.th>
                    <x-table.th>Blood Group</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td>
                            <span class="font-bold text-violet-600 dark:text-violet-400">{{ $patient->uhid }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-xs"
                                     style="background:#7c3aed">
                                    {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name ?? '', 0, 1)) }}
                                </div>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $patient->full_name }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $patient->gender }}</span>
                                <span class="text-xs font-bold text-gray-500 uppercase">{{ $patient->age }} Years</span>
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $patient->phone }}</span>
                        </td>
                        <td>
                            @if($patient->blood_group)
                                <x-badge color="danger">{{ $patient->blood_group }}</x-badge>
                            @else
                                <span class="text-xs text-gray-400 italic">Not Added</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('counter.opd.index', ['patient_id' => $patient->id]) }}"
                                   class="btn btn-ghost px-2 py-2 text-indigo-600" title="Create OP Visit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </a>
                                <a href="{{ route('counter.patients.history', $patient->id) }}"
                                   class="btn btn-ghost px-2 py-2" title="View History">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </a>
                                <button @click="$dispatch('edit-patient', { id: {{ $patient->id }} })"
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deletePatient({{ $patient->id }})"
                                        wire:confirm="Are you sure you want to delete this patient record?"
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No patients found matching your search." />
                @endforelse
            </tbody>
        </x-table.wrapper>
        </div>

        @if($patients->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $patients->links() }}
            </div>
        @endif
    </x-card>

    <livewire:counter.patient-form />
</div>
