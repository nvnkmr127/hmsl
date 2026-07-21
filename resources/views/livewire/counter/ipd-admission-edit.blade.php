<x-modal name="edit-admission-modal" title="Edit Admission" width="3xl" persistent>
    <div class="p-6" x-data="{}">
        @if($admission)
        <div class="mb-8 relative">
            <x-clinical.patient-strip :patient="$admission->patient" size="md" />
        </div>
        
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column (Admission Details & Vitals) -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Admission Details
                    </h4>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Select Doctor</label>
                        <select wire:model="doctorId" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white appearance-none transition-all outline-none">
                            <option value="">Select Doctor...</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                            @endforeach
                        </select>
                        @error('doctorId') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Admission Date & Time</label>
                        <input type="datetime-local" wire:model="admissionDate" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white transition-all outline-none" />
                        @error('admissionDate') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Admission Number</label>
                        <div class="flex items-stretch rounded-2xl bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus-within:border-indigo-500 transition-all">
                            <input 
                                type="text" 
                                wire:model.live.debounce.500ms="manualAdmissionNumber" 
                                placeholder="ENTER NUMBER..." 
                                class="flex-1 bg-transparent border-none focus:ring-0 pl-5 pr-5 py-4 font-bold text-gray-900 dark:text-white text-sm outline-none uppercase tracking-wide"
                            />
                        </div>
                        @error('manualAdmissionNumber') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5" x-data="{ open: false, search: @entangle('reason') }">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Reason for Admission</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live="reason" 
                                @focus="open = true"
                                @click.away="open = false"
                                class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all" 
                                placeholder="TYPE OR SELECT REASON..."
                            />
                            <div x-show="open && (search ? search.length : 0) >= 0" class="absolute z-50 left-0 right-0 mt-2 p-2 bg-white dark:bg-gray-900 rounded-2xl shadow-3xl border border-gray-100 dark:border-gray-800 max-h-48 overflow-y-auto custom-scrollbar">
                                @foreach($reasons as $r)
                                    <button 
                                        type="button"
                                        @click="search = '{{ $r->content }}'; open = false"
                                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wide transition-colors"
                                    >
                                        {{ $r->content }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('reason') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">WT (kg)</label>
                            <input type="number" step="0.1" wire:model="weight" placeholder="0.0" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">HT (cm)</label>
                            <input type="number" step="0.1" wire:model="height" placeholder="0.0" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Pulse (bpm)</label>
                            <input type="number" wire:model="pulse" placeholder="72" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">BP (Sys/Dia)</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="bp_systolic" placeholder="120" class="w-1/2 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-2 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm text-center" />
                                <span class="self-center text-gray-400 font-black">/</span>
                                <input type="text" wire:model="bp_diastolic" placeholder="80" class="w-1/2 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-2 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm text-center" />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Resp. Rate</label>
                            <input type="number" wire:model="resp_rate" placeholder="18" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">SpO2 (%)</label>
                            <input type="number" wire:model="spo2" placeholder="98" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Right Column (Ward & Bed) -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Accommodation Details
                    </h4>
                    <div class="space-y-4 bg-gray-50 dark:bg-gray-900/50 p-5 rounded-3xl border border-gray-100 dark:border-gray-800">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Select Ward</label>
                            <select wire:model.live="wardId" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white appearance-none transition-all outline-none">
                                <option value="">Select Ward...</option>
                                @foreach($wards as $ward)
                                    <option value="{{ $ward->id }}">{{ $ward->name }} ({{ $ward->daily_charge ? '₹'.number_format($ward->daily_charge) : 'No Charge' }})</option>
                                @endforeach
                            </select>
                            @error('wardId') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3">
                             <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1 block">Choose a Bed</label>
                             <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                 @forelse($this->availableBeds as $bed)
                                     <button 
                                         type="button"
                                         wire:click="$set('bedId', {{ $bed->id }})"
                                         class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all {{ $bedId == $bed->id ? 'bg-indigo-600 border-indigo-600 text-white shadow-xl shadow-indigo-500/30' : 'bg-white dark:bg-gray-950 border-gray-100 dark:border-gray-800 hover:border-indigo-300' }}"
                                     >
                                         <svg class="w-6 h-6 mb-1 opacity-60 group-hover:opacity-100 transition-opacity {{ $bedId == $bed->id ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                         <span class="text-[11px] font-black text-center leading-tight {{ $bedId == $bed->id ? 'text-white' : 'text-gray-700 dark:text-gray-300' }}">{{ $bed->bed_number }}</span>
                                         @if($bedId == $bed->id)
                                             <div class="absolute -top-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center shadow">
                                                 <svg class="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg>
                                             </div>
                                         @endif
                                     </button>
                                 @empty
                                     <div class="col-span-full py-6 bg-gray-50 dark:bg-gray-950 rounded-2xl border-2 border-dashed border-gray-100 dark:border-gray-800 text-center">
                                         <p class="text-xs text-gray-400 font-black uppercase tracking-widest">{{ !$wardId ? 'Select ward first' : 'No beds available' }}</p>
                                     </div>
                                 @endforelse
                             </div>
                             @error('bedId') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                         </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-4">
                <button type="button" @click="$dispatch('close-modal', { name: 'edit-admission-modal' })" 
                        class="px-6 py-3 text-tiny font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 transition-all">
                    Cancel
                </button>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="btn btn-primary px-10 py-4 shadow-xl shadow-indigo-500/30 rounded-2xl group transition-all active:scale-95">
                    <span wire:loading.remove>Save Changes</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
        @endif
    </div>
</x-modal>
