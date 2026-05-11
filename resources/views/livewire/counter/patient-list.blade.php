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
                <div class="p-5 hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-950/40 text-[9px] font-black text-indigo-600 dark:text-indigo-400 rounded uppercase tracking-widest border border-indigo-100 dark:border-indigo-900/30">{{ $patient->uhid }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $patient->gender }} · {{ $patient->age }}Y</span>
                            </div>
                            <a href="{{ route('counter.patients.history', $patient->id) }}" class="text-base font-black text-gray-900 dark:text-white truncate block tracking-tight uppercase hover:text-indigo-600 transition-colors">{{ $patient->full_name }}</a>
                            
                            <div class="flex flex-col gap-1 mt-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span class="text-xs font-black text-gray-600 dark:text-gray-400 tracking-wider">{{ $patient->phone }}</span>
                                </div>
                                @if($patient->latestConsultation)
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Last: {{ $patient->latestConsultation->consultation_date->format('d M') }} · {{ $patient->latestConsultation->doctor?->full_name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button @click="$dispatch('quick-op-booking', { patient_id: {{ $patient->id }} })" 
                                    class="p-3 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-500/20 active:scale-95 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                            </button>
                            <a href="{{ route('counter.patients.history', $patient->id) }}" 
                               class="p-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-2xl active:scale-95 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            @if($patient->latestConsultation)
                                @can('view opd')
                                    <button @click="$dispatch('quick-op-booking', { edit_id: {{ $patient->latestConsultation->id }} })" class="px-3 py-2 text-[10px] font-black text-amber-600 uppercase tracking-widest hover:bg-amber-50 dark:hover:bg-amber-950/20 rounded-lg transition-colors">Edit OP</button>
                                @endcan
                                <a href="{{ route('counter.opd.print', ['id' => $patient->latestConsultation->id]) }}" target="_blank" class="px-3 py-2 text-[10px] font-black text-emerald-600 uppercase tracking-widest hover:bg-emerald-50 dark:hover:bg-emerald-950/20 rounded-lg transition-colors">Print Slip</a>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="$dispatch('edit-patient', { id: {{ $patient->id }} })" class="px-3 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors">Edit</button>
                            <button wire:click="deletePatient({{ $patient->id }})" wire:confirm="Are you sure?" class="px-3 py-2 text-[10px] font-black text-rose-400 uppercase tracking-widest hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-colors">Delete</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No patients found</p>
                </div>
            @endforelse
        </div>

        <div class="hidden md:block">
            <x-table.wrapper>
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-800">
                        <x-table.th class="py-5 pl-8">Patient Profile</x-table.th>
                        <x-table.th>Contact Information</x-table.th>
                        <x-table.th>Last Clinical Visit</x-table.th>
                        <x-table.th class="text-right pr-8">Actions</x-table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-900">
                    @forelse($patients as $patient)
                        <tr class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-950/10 transition-all duration-200">
                            <td class="py-5 pl-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-sm shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform duration-300"
                                         style="background: linear-gradient(135deg, #6366f1, #4338ca)">
                                        {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <a href="{{ route('counter.patients.history', $patient->id) }}" class="font-black text-gray-900 dark:text-white uppercase tracking-tight hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $patient->full_name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-[9px] font-black text-gray-500 dark:text-gray-400 rounded uppercase tracking-widest border border-gray-200/50 dark:border-gray-700/50">{{ $patient->uhid }}</span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">· {{ $patient->gender }} · {{ $patient->age }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span class="font-black text-gray-700 dark:text-gray-300 tracking-wider">{{ $patient->phone }}</span>
                                    </div>
                                    @if($patient->address)
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest truncate max-w-[150px] mt-0.5">{{ $patient->city ?: 'No Address' }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($patient->latestConsultation)
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                            <span class="font-black text-gray-900 dark:text-white text-xs tracking-tight">{{ $patient->latestConsultation->consultation_date->format('d M, Y') }}</span>
                                        </div>
                                        <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-0.5">{{ $patient->latestConsultation->doctor?->full_name ?? 'Resident' }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-700"></div>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">New Patient</span>
                                    </div>
                                @endif
                            </td>

                            <td class="pr-8 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Primary Medical Actions Group -->
                                    <div class="flex items-center bg-gray-100/50 dark:bg-gray-800/50 p-1 rounded-xl gap-0.5 border border-gray-200/50 dark:border-gray-700/50">
                                        <button @click="$dispatch('quick-op-booking', { patient_id: {{ $patient->id }} })"
                                           class="btn btn-ghost px-2.5 py-2.5 text-indigo-600 hover:bg-white dark:hover:bg-gray-900 shadow-sm transition-all" title="Create OP Visit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                                        </button>
                                        
                                        <a href="{{ route('counter.patients.history', $patient->id) }}"
                                           class="btn btn-ghost px-2.5 py-2.5 text-slate-600 hover:bg-white dark:hover:bg-gray-900 shadow-sm transition-all" title="Full Medical History">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                        </a>

                                        @if($patient->latestConsultation)
                                            @can('view opd')
                                                <button @click="$dispatch('quick-op-booking', { edit_id: {{ $patient->latestConsultation->id }} })"
                                                   class="btn btn-ghost px-2.5 py-2.5 text-amber-600 hover:bg-white dark:hover:bg-gray-900 shadow-sm transition-all" title="Edit Latest OP Visit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                </button>
                                            @endcan
                                            <a href="{{ route('counter.opd.print', ['id' => $patient->latestConsultation->id]) }}"
                                               target="_blank"
                                               class="btn btn-ghost px-2.5 py-2.5 text-emerald-600 hover:bg-white dark:hover:bg-gray-900 shadow-sm transition-all" title="Print Latest OP Slip">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4" /></svg>
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Administrative Actions Group -->
                                    <div class="flex items-center gap-0.5 ml-2 border-l border-gray-100 dark:border-gray-800 pl-2">
                                        <button @click="$dispatch('edit-patient', { id: {{ $patient->id }} })"
                                                class="btn btn-ghost px-2.5 py-2.5 text-violet-500 hover:bg-violet-50 dark:hover:bg-violet-950/20 transition-all" title="Edit Patient Info">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </button>
                                        <button wire:click="deletePatient({{ $patient->id }})"
                                                wire:confirm="Permanent Delete: Are you sure you want to remove this patient record?"
                                                class="btn btn-ghost px-2.5 py-2.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all" title="Delete Permanent">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
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

</div>
