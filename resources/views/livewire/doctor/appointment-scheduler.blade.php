<div>
    <x-modal name="appointment-modal" title="Schedule Appointment" width="md">
        <div class="space-y-6">
            @if(!$selectedPatient)
                <div class="space-y-4">
                    <x-form.input 
                        label="Find Patient" 
                        placeholder="Search Name or UHID..." 
                        wire:model.live.debounce.300ms="searchPatient"
                    />

                    @if(count($patients))
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                            @foreach($patients as $p)
                                <button wire:click="selectPatient({{ $p->id }})" class="w-full px-4 py-3 text-left hover:bg-violet-50 dark:hover:bg-violet-900/10 flex items-center justify-between group transition-all border-b border-gray-100 dark:border-gray-800 last:border-0 text-sm">
                                    <span>{{ $p->full_name }} <span class="text-xs text-gray-400 ml-2">{{ $p->uhid }}</span></span>
                                    <svg class="w-4 h-4 text-violet-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="p-4 rounded-2xl bg-violet-50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-violet-600 dark:text-violet-400 uppercase tracking-[0.2em] mb-1">Selected Patient</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $selectedPatient->full_name }}</p>
                    </div>
                    <button wire:click="$set('selectedPatient', null)" class="text-xs font-bold text-red-500 hover:underline">Change</button>
                </div>

                <form wire:submit="schedule" class="space-y-4">
                    <x-form.input label="Consultation Date" type="date" wire:model="consultation_date" id="sch-date" />
                    
                    <x-form.textarea label="Clinical Notes" wire:model="notes" placeholder="Reason for appointment..." id="sch-notes" rows="3" />

                    <div class="flex flex-col gap-3 pt-4">
                        <button type="submit" class="btn btn-primary w-full py-3">Confirm Appointment</button>
                        <button type="button" @click="$dispatch('close-modal', { name: 'appointment-modal' })" class="btn btn-ghost w-full">Cancel</button>
                    </div>
                </form>
            @endif
        </div>
    </x-modal>
</div>
