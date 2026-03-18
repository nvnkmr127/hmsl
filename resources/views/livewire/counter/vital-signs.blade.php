<div>
    <x-modal name="vitals-modal" :title="'Record Vitals: ' . $patientName" width="3xl">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Anthropometry -->
                <div class="space-y-4">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-indigo-50 dark:border-indigo-900/30 pb-2 text-center">Pediatric Stats</h3>
                    
                    <div class="p-4 bg-orange-50 dark:bg-orange-950/20 rounded-2xl border-2 border-orange-100 dark:border-orange-900/30 text-center">
                        <p class="text-[10px] font-black text-orange-600 dark:text-orange-400 uppercase tracking-widest mb-1">Weight is Critical</p>
                        <x-form.input label="Weight (kg)" wire:model="weight" name="weight" type="number" step="0.01" class="text-center text-lg font-black" />
                    </div>

                    <x-form.input label="Height (cm)" wire:model="height" name="height" type="number" step="0.01" />
                    
                    <div class="p-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/50">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Calculated BMI</span>
                        <div class="text-xl font-black text-gray-800 dark:text-white">
                            @if($weight && $height && $height > 0)
                                {{ round($weight / (($height/100) * ($height/100)), 1) }}
                            @else
                                --
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Core Vitals -->
                <div class="space-y-4">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-indigo-50 dark:border-indigo-900/30 pb-2">Core Vitals</h3>
                    <x-form.input label="Temp (°F)" wire:model="temperature" name="temperature" type="number" step="0.1" />
                    <x-form.input label="Pulse (bpm)" wire:model="pulse" name="pulse" type="number" />
                    <div class="grid grid-cols-2 gap-2">
                        <x-form.input label="BP (Sys)" wire:model="bp_systolic" name="bp_systolic" placeholder="120" />
                        <x-form.input label="BP (Dia)" wire:model="bp_diastolic" name="bp_diastolic" placeholder="80" />
                    </div>
                </div>

                <!-- Respiratory & Others -->
                <div class="space-y-4">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-indigo-50 dark:border-indigo-900/30 pb-2">Respiratory</h3>
                    <x-form.input label="Resp. Rate" wire:model="resp_rate" name="resp_rate" type="number" />
                    <x-form.input label="SpO2 (%)" wire:model="spo2" name="spo2" type="number" />
                    <x-form.input label="Blood Sugar" wire:model="blood_sugar" name="blood_sugar" type="number" step="0.01" />
                </div>
            </div>

            <div>
                <x-form.textarea label="Additional Clinical Observations" wire:model="notes" name="notes" placeholder="Any obvious distress, pallor, etc." />
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                <button type="button" @click="$dispatch('close-modal', { name: 'vitals-modal' })" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="btn-primary px-8 py-2.5">
                    Save Vitals
                </button>
            </div>
        </form>
    </x-modal>
</div>
