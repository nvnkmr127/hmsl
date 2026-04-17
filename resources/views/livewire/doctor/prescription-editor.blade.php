<div>
    <x-modal name="prescription-modal" title="Prescription Editor" width="5xl">
        <form wire:submit="save" class="space-y-6">

            {{-- Header Info --}}
            <div class="bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/30 rounded-2xl px-5 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest">Patient</p>
                    <p class="font-bold text-gray-800 dark:text-white text-sm">{{ $patientName }}</p>
                </div>
                @if($existingPrescription)
                    <div class="ml-auto">
                        <a href="{{ route('counter.prescriptions.print', $existingPrescription) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 px-3 py-1.5 rounded-lg bg-indigo-100/60 dark:bg-indigo-900/30 hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print Rx
                        </a>
                    </div>
                @endif
            </div>

            {{-- Clinical Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1.5 pl-1">Chief Complaint</label>
                    <textarea wire:model="chief_complaint" rows="3" id="rx-complaint"
                        placeholder="e.g., Fever, headache for 3 days…"
                        class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 px-4 py-3 sm:text-sm resize-none transition-all duration-300"></textarea>
                    @error('chief_complaint') <p class="text-red-500 text-xs mt-1 pl-2">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1.5 pl-1">Diagnosis / Impression</label>
                    <textarea wire:model="diagnosis" rows="3" id="rx-diagnosis"
                        placeholder="e.g., Viral fever, URTI…"
                        class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 px-4 py-3 sm:text-sm resize-none transition-all duration-300"></textarea>
                    @error('diagnosis') <p class="text-red-500 text-xs mt-1 pl-2">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Medicines --}}
            <div class="bg-gray-50/50 dark:bg-gray-950/50 p-5 rounded-2xl border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-black text-violet-600 uppercase tracking-widest">℞ Medicines</p>
                    <button type="button" wire:click="addMedicine"
                            class="btn btn-ghost text-violet-600 text-xs font-bold uppercase py-2 h-auto">
                        + Add Medicine
                    </button>
                </div>

                @if(count($medicines) === 0)
                    <p class="text-sm text-gray-400 text-center py-4">No medicines added. Click "+ Add Medicine" to begin.</p>
                @else
                    <div class="space-y-3">
                        @foreach($medicines as $idx => $med)
                            <div wire:key="rx-med-{{ $idx }}" class="grid grid-cols-12 gap-2 items-start p-3 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50">
                                <div class="col-span-3">
                                    <input wire:model.live.debounce.300ms="medicines.{{ $idx }}.name"
                                        list="medicine-list-{{ $idx }}"
                                        placeholder="Medicine name"
                                        class="block w-full rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 px-3 py-2 transition-all" />
                                    <datalist id="medicine-list-{{ $idx }}">
                                        @foreach($medicineList as $m)
                                            <option value="{{ $m->name }}{{ $m->strength ? ' ' . $m->strength : '' }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-span-2">
                                    <input wire:model.live.debounce.300ms="medicines.{{ $idx }}.dose"
                                        placeholder="Dose (e.g. 500mg)"
                                        class="block w-full rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 px-3 py-2 transition-all" />
                                </div>
                                <div class="col-span-2">
                                    <select wire:model="medicines.{{ $idx }}.frequency"
                                        class="block w-full rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 px-3 py-2 transition-all">
                                        <option>Once a day</option>
                                        <option>Twice a day</option>
                                        <option>Thrice a day</option>
                                        <option>Four times a day</option>
                                        <option>At bedtime</option>
                                        <option>SOS</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <input wire:model.live.debounce.300ms="medicines.{{ $idx }}.duration"
                                        placeholder="Duration"
                                        class="block w-full rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 px-3 py-2 transition-all" />
                                </div>
                                <div class="col-span-1">
                                    <input wire:model.live.debounce.300ms="medicines.{{ $idx }}.qty"
                                        type="number"
                                        min="1"
                                        placeholder="Qty"
                                        class="block w-full rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 px-3 py-2 transition-all" />
                                </div>
                                <div class="col-span-1">
                                    <input wire:model.live.debounce.300ms="medicines.{{ $idx }}.instructions"
                                        placeholder="Instructions (e.g. After food)"
                                        class="block w-full rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 px-3 py-2 transition-all" />
                                </div>
                                <div class="col-span-1 flex justify-center pt-2">
                                    <button type="button" wire:click="removeMedicine({{ $idx }})" wire:loading.attr="disabled" wire:target="removeMedicine({{ $idx }})"
                                            class="text-gray-300 hover:text-rose-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Advice & Follow-up --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1.5 pl-1">General Advice</label>
                    <textarea wire:model="advice" rows="3" id="rx-advice"
                        placeholder="e.g., Rest, plenty of fluids, avoid cold items…"
                        class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 px-4 py-3 sm:text-sm resize-none transition-all duration-300"></textarea>
                </div>
                <div>
                    <x-form.input
                        wire:model="follow_up_date"
                        type="date"
                        label="Follow-up Date"
                        id="rx-followup"
                    />
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="flex gap-2">
                    @if($existingPrescription)
                        <button type="button" 
                            wire:click="print({{ $existingPrescription }})"
                            class="btn btn-ghost text-emerald-600 font-bold px-4">
                            Print
                        </button>
                        <button type="button" 
                            wire:click="sendEmail"
                            wire:loading.attr="disabled"
                            class="btn btn-ghost text-indigo-600 font-bold px-4">
                            <span wire:loading.remove wire:target="sendEmail">Email Patient</span>
                            <span wire:loading wire:target="sendEmail">Sending...</span>
                        </button>
                    @endif
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="$dispatch('close-modal', { name: 'prescription-modal' })"
                            class="btn btn-ghost px-6">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary px-10">
                        {{ $existingPrescription ? 'Update Prescription' : 'Save Prescription' }}
                    </button>
                </div>
            </div>

        </form>
    </x-modal>
</div>
