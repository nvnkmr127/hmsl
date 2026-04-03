<div>
    <x-page-header
        title="Patient Registry"
        subtitle="View and manage all registered patients in the system.">
        <x-slot name="actions">
            <button wire:click="downloadExport" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export
            </button>
            <button @click="$dispatch('create-patient')" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Register Patient
            </button>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="p-6 bg-white dark:bg-gray-900 rounded-ultra border border-gray-100 dark:border-gray-800 shadow-sm">
            <p class="text-tiny font-black text-gray-400 uppercase tracking-widest mb-1">Total Patients</p>
            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</h3>
            <p class="text-tiny font-bold text-violet-600 mt-2 uppercase">Life-time registry</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-900 rounded-ultra border border-gray-100 dark:border-gray-800 shadow-sm">
            <p class="text-tiny font-black text-gray-400 uppercase tracking-widest mb-1">New Registrations</p>
            <h3 class="text-3xl font-black text-emerald-600">{{ number_format($stats['today']) }}</h3>
            <p class="text-tiny font-bold text-gray-400 mt-2 uppercase">Registered Today</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-900 rounded-ultra border border-gray-100 dark:border-gray-800 shadow-sm">
            <p class="text-tiny font-black text-gray-400 uppercase tracking-widest mb-1">Gender Dist.</p>
            <div class="flex items-center gap-4 mt-2">
                <div>
                    <p class="text-xs font-black text-blue-500 uppercase">M: {{ $stats['male'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-black text-pink-500 uppercase">F: {{ $stats['female'] }}</p>
                </div>
            </div>
            <p class="text-tiny font-bold text-gray-400 mt-2 uppercase">Current breakdown</p>
        </div>
        <div class="p-6 bg-violet-600 rounded-ultra shadow-lg shadow-violet-500/20 text-white relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-tiny font-black text-violet-200 uppercase tracking-widest mb-1">Quick Booking</p>
                <h3 class="text-xl font-black leading-tight">Generate<br>OP Token</h3>
                <p class="text-tiny font-black text-white/70 mt-2 uppercase">Search to begin</p>
            </div>
            <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        </div>
    </div>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5">
                    <x-form.input
                        placeholder="Quick search..."
                        wire:model.live.debounce.300ms="search"
                        icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>
                <div class="md:col-span-2">
                    <x-form.select wire:model.live="genderFilter">
                        <option value="">Any Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </x-form.select>
                </div>
                <div class="md:col-span-2">
                    <x-form.select wire:model.live="bloodGroupFilter">
                        <option value="">All Blood Type</option>
                        @foreach($bloodGroups as $bg)
                            <option value="{{ $bg }}">{{ $bg }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="md:col-span-3">
                    <x-form.select wire:model.live="sortBy">
                        <option value="latest">Recently Added</option>
                        <option value="alphabetic">A-Z Name</option>
                    </x-form.select>
                </div>
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
                    <x-table.th>Patient Profile</x-table.th>
                    <x-table.th>Gender / Age</x-table.th>
                    <x-table.th>Phone</x-table.th>
                    <x-table.th>Blood Group</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-sm shadow-inner"
                                     style="background: linear-gradient(135deg, #7c3aed, #4c1d95)">
                                    {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name ?? '', 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $patient->full_name }}</span>
                                    <span class="text-tiny font-black text-violet-600 dark:text-violet-400 uppercase tracking-widest">{{ $patient->uhid }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $patient->gender }}</span>
                                <span class="text-tiny font-black text-gray-400 uppercase tracking-widest">{{ $patient->age }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $patient->phone }}</span>
                        </td>
                        <td>
                            @if($patient->blood_group)
                                <x-badge color="danger">{{ $patient->blood_group }}</x-badge>
                            @else
                                <span class="text-tiny font-black text-gray-400 uppercase tracking-widest italic opacity-40">N/A</span>
                            @endif
                        </td>
                        <td>
                            <x-badge :color="$patient->is_active ? 'success' : 'warning'">
                                {{ $patient->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('counter.opd.index', ['patient_id' => $patient->id]) }}"
                                   class="btn btn-ghost px-2.5 py-2.5 text-indigo-600 hover:bg-indigo-600/10" title="Create OP Visit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </a>
                                <a href="{{ route('counter.patients.history', $patient->id) }}"
                                   class="btn btn-ghost px-2.5 py-2.5 text-slate-600 hover:bg-slate-600/10" title="View History">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </a>
                                <button @click="$dispatch('edit-patient', { id: {{ $patient->id }} })"
                                        class="btn btn-ghost px-2.5 py-2.5 text-violet-600 hover:bg-violet-600/10" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deletePatient({{ $patient->id }})"
                                        wire:confirm="Are you sure you want to delete this patient record?"
                                        class="btn btn-ghost px-2.5 py-2.5 text-red-500 hover:bg-red-500/10" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No patients found in our system matching these criteria." />
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
