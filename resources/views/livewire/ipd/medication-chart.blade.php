<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Medication Chart</h3>
        @unless($admission->status === 'Discharged')
            <button wire:click="$toggle('showForm')" class="btn btn-primary text-xs" wire:loading.attr="disabled">
                <svg wire:loading class="w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Add Medicine
            </button>
        @endunless
    </div>

    <div class="mb-4 border-b border-gray-100 dark:border-gray-800">
        <nav class="flex gap-1">
            <button wire:click="setTab('active')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'active' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Active ({{ $this->activeMedications->count() }})
            </button>
            <button wire:click="setTab('stopped')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'stopped' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Stopped ({{ $this->stoppedMedications->count() }})
            </button>
            <button wire:click="setTab('completed')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'completed' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Completed ({{ $this->completedMedications->count() }})
            </button>
            <button wire:click="setTab('all')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'all' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                All ({{ $this->allMedications->count() }})
            </button>
        </nav>
    </div>

    @if($this->showForm)
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl mb-4">
            <h4 class="font-bold text-gray-900 dark:text-white mb-3">{{ $editingId ? 'Edit' : 'Add' }} Medicine</h4>

            <div class="mb-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Medicine Name</label>
                <div class="relative">
                    <input type="text" wire:model.live="searchMedicine" wire:focus="$set('showMedicineSearch', true)" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Search medicine...">
                    @if($this->showMedicineSearch && strlen($this->searchMedicine) >= 2 && $medicines && $medicines->count() > 0)
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xl max-h-48 overflow-y-auto">
                            @foreach($medicines as $med)
                                <button wire:click="selectMedicine({{ $med->id }})" wire:key="med-{{ $med->id }}" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm flex justify-between items-center">
                                    <span class="font-semibold">{{ $med->name }}</span>
                                    @if($med->strength)
                                        <span class="text-gray-500 text-xs">{{ $med->strength }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <input type="hidden" wire:model="medicine_name">
                @error('medicine_name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Dosage</label>
                    <input type="text" wire:model="dosage" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., 500mg">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Frequency</label>
                    <select wire:model="frequency" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                        <option value="">Select</option>
                        @foreach($frequencyOptions as $freq)
                            <option value="{{ $freq }}">{{ $freq }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Route</label>
                    <select wire:model="route" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                        @foreach($routeOptions as $route)
                            <option value="{{ $route }}">{{ $route }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Duration</label>
                    <input type="text" wire:model="duration" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., 5 days">
                </div>
            </div>

            <div class="mb-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Start Date</label>
                <input type="date" wire:model="start_date" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
            </div>

            <div class="mb-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Instructions</label>
                <input type="text" wire:model="instructions" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., After food">
            </div>

            <div class="flex justify-end gap-2">
                <button wire:click="resetForm" class="btn btn-secondary text-xs">Cancel</button>
                <button wire:click="save" class="btn btn-primary text-xs">{{ $editingId ? 'Update' : 'Add' }} Medicine</button>
            </div>
        </div>
    @endif

    @if($stoppingId)
        <x-modal name="stop-medication-modal" title="Stop Medication">
            <div class="p-4">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Reason for stopping</label>
                <textarea wire:model="stopReason" rows="3" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Enter reason..."></textarea>
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="$dispatch('close-modal', { name: 'stop-medication-modal' })" class="btn btn-secondary">Cancel</button>
                    <button wire:click="stopMedication" class="btn btn-rose">Stop Medication</button>
                </div>
            </div>
        </x-modal>
    @endif

    <div class="space-y-3">
        @forelse(($activeTab === 'active' ? $this->activeMedications : ($activeTab === 'stopped' ? $this->stoppedMedications : ($activeTab === 'completed' ? $this->completedMedications : $this->allMedications))) as $med)
            <div class="p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl {{ $med->status === 'Stopped' ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-bold text-gray-900 dark:text-white">{{ $med->medicine_name }}</h4>
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $med->status === 'Active' ? 'bg-emerald-100 text-emerald-700' : ($med->status === 'Stopped' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $med->status }}
                            </span>
                            @if($med->is_dispensed)
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">Dispensed</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @if($med->dosage){{ $med->dosage }}@endif
                            @if($med->frequency) · {{ $med->frequency }}@endif
                            @if($med->route) · {{ $med->route }}@endif
                            @if($med->duration) · {{ $med->duration }}@endif
                        </p>
                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                            <span>Start: {{ $med->start_date ? $med->start_date->format('d M Y') : 'N/A' }}</span>
                            @if($med->end_date)
                                <span>End: {{ $med->end_date->format('d M Y') }}</span>
                            @endif
                        </div>
                        @if($med->instructions)
                            <p class="text-xs text-gray-500 mt-1"><span class="font-bold">Instructions:</span> {{ $med->instructions }}</p>
                        @endif
                        @if($med->status === 'Stopped' && $med->stop_reason)
                            <p class="text-xs text-rose-500 mt-1"><span class="font-bold">Stop Reason:</span> {{ $med->stop_reason }}</p>
                        @endif

                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-500 bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded">
                                Doses Given: {{ $med->administrations_count ?? $med->medicationAdministrations->count() }}
                            </span>
                        </div>
                    </div>
                    @if($med->status === 'Active' && $admission->status !== 'Discharged')
                        <div class="flex items-center gap-2">
                            <button wire:click="administerDose({{ $med->id }})" class="btn btn-emerald text-[10px] py-1 px-2" title="Record Administration">
                                Administer
                            </button>
                            <button wire:click="editMedication({{ $med->id }})" class="p-2 text-gray-400 hover:text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="confirmStop({{ $med->id }})" class="p-2 text-gray-400 hover:text-rose-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <p class="text-sm">No medications in this category.</p>
            </div>
        @endforelse
    </div>
</div>
