<div class="max-w-4xl mx-auto">
    <x-page-header 
        title="Patient Admission" 
        subtitle="Register a new IPD admission and assign a ward bed facility."
        back="{{ route('counter.ipd.index') }}"
    />

    <x-card>
        <form wire:submit="save" class="space-y-8">
            <!-- Patient Selection -->
            <div class="space-y-4">
                <p class="section-lbl" style="color:#7c3aed">1. Select Patient</p>
                @if(!$patientId)
                    <div class="relative">
                        <x-form.input 
                            label="Search Patient" 
                            wire:model.live.debounce.300ms="searchPatient" 
                            placeholder="Type Name or UHID (min 3 chars)..." 
                            icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                        @if(!empty($patients))
                            <div class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-950 rounded-xl shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                @foreach($patients as $patient)
                                    <button type="button" wire:click="selectPatient({{ $patient->id }})" class="w-full px-5 py-4 text-left hover:bg-violet-50 dark:hover:bg-violet-900/10 flex items-center justify-between border-b last:border-0 border-gray-50 dark:border-gray-900 transition-colors">
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ $patient->full_name }}</p>
                                            <p class="text-[10px] text-gray-500 font-bold tracking-widest mt-0.5">{{ $patient->uhid }}</p>
                                        </div>
                                        <x-badge color="violet">{{ $patient->gender }}</x-badge>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-5 rounded-2xl border border-violet-100 dark:border-violet-900/30 flex items-center justify-between" 
                         style="background:rgba(124,58,237,0.04)">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-lg" 
                                 style="background:#7c3aed">
                                {{ strtoupper(substr($patientName, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $patientName }}</h4>
                                <p class="text-[10px] text-violet-600 dark:text-violet-400 font-bold tracking-widest">SELECTED PATIENT</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('patientId', null)" class="btn btn-ghost text-red-500 text-xs uppercase tracking-widest px-3 py-1.5 h-auto">Change</button>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Clinical Assign -->
                <div class="space-y-6">
                    <p class="section-lbl" style="color:#7c3aed">2. Clinical Assign</p>
                    <x-form.select label="Consulting Doctor" wire:model="doctorId">
                        <option value="">Select Doctor...</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">Dr. {{ $doctor->full_name }} ({{ $doctor->department?->name ?? 'No Department' }})</option>
                        @endforeach
                    </x-form.select>
                    
                    <x-form.input label="Admission Date/Time" type="datetime-local" wire:model="admissionDate" />
                    <x-form.input label="Primary Reason" wire:model="reason" placeholder="e.g. Schedule Surgery, Intensive Care, etc." />
                </div>

                <!-- Ward & Bed Assignment -->
                <div class="space-y-6">
                    <p class="section-lbl" style="color:#7c3aed">3. Ward & Bed assignment</p>
                    <x-form.select label="Target Ward" wire:model.live="wardId">
                        <option value="">Select Ward...</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->id }}">{{ $ward->name }} ({{ $ward->type }})</option>
                        @endforeach
                    </x-form.select>

                    <div class="p-5 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <label class="section-lbl mb-4 block">Select Available Bed</label>
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                            @forelse($this->availableBeds as $bed)
                                <button 
                                    type="button"
                                    wire:click="$set('bedId', {{ $bed->id }})"
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all {{ $bedId == $bed->id ? 'bg-violet-600 border-violet-600 text-white shadow-lg shadow-violet-500/30' : 'bg-white dark:bg-gray-950 border-gray-100 dark:border-gray-800 hover:border-violet-300' }}"
                                >
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    <span class="text-[10px] font-bold">{{ $bed->bed_number }}</span>
                                </button>
                            @empty
                                <div class="col-span-full py-6 text-center">
                                    <p class="text-xs text-gray-500 font-medium italic">Please select a ward above to see beds.</p>
                                </div>
                            @endforelse
                        </div>
                        @error('bedId') <p class="text-[10px] text-red-500 font-bold mt-2 uppercase">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <x-form.textarea label="Clinical Notes" wire:model="notes" name="notes" placeholder="Any additional instructions or observations..." rows="3" />

            <!-- Submission -->
            <div class="pt-8 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                <a href="{{ route('counter.ipd.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary px-12">
                    Confirm Admission
                </button>
            </div>
        </form>
    </x-card>
</div>
