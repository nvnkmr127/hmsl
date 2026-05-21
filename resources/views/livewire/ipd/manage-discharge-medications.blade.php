<div>
    <button x-data @click="$dispatch('open-modal', { name: 'manage-discharge-meds-modal' })" class="btn btn-primary btn-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Manage Medications
    </button>

    <x-modal name="manage-discharge-meds-modal" title="Manage Discharge Medications" maxWidth="6xl">
        <div class="p-6">
            <div class="bg-indigo-50 dark:bg-indigo-900/30 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800 mb-6 flex items-start gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-indigo-900 dark:text-indigo-300">Medication Guidelines</h4>
                    <p class="text-xs text-indigo-700 dark:text-indigo-400 mt-1">Add all discharge medications here. Frequency and instructions are pre-configured to print in both English and Telugu to ensure patient understanding.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-3 rounded-tl-lg">Medicine Name</th>
                            <th class="px-4 py-3">Dosage</th>
                            <th class="px-4 py-3">Route (Intake Type)</th>
                            <th class="px-4 py-3">Frequency (Times)</th>
                            <th class="px-4 py-3">Duration</th>
                            <th class="px-4 py-3">Instructions</th>
                            <th class="px-4 py-3 rounded-tr-lg"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($meds as $index => $med)
                            <tr>
                                <td class="px-2 py-2">
                                    <input type="text" wire:model="meds.{{ $index }}.medicine_name" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Paracetamol">
                                    @error("meds.{$index}.medicine_name") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" wire:model="meds.{{ $index }}.dosage" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 500mg">
                                </td>
                                <td class="px-2 py-2">
                                    <select wire:model="meds.{{ $index }}.route" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="Oral">Oral (నోటి ద్వారా)</option>
                                        <option value="IV">IV (సిర ద్వారా)</option>
                                        <option value="IM">IM (కండరం ద్వారా)</option>
                                        <option value="Topical">Topical (పైపూతగా)</option>
                                        <option value="Drops">Drops (చుక్కలు)</option>
                                    </select>
                                </td>
                                <td class="px-2 py-2">
                                    <select wire:model="meds.{{ $index }}.frequency" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Frequency...</option>
                                        @foreach($frequencies as $key => $label)
                                            <option value="{{ $label }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error("meds.{$index}.frequency") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" wire:model="meds.{{ $index }}.duration" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 5 Days">
                                </td>
                                <td class="px-2 py-2">
                                    <select wire:model="meds.{{ $index }}.instructions" class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Instruction...</option>
                                        @foreach($instructionsList as $key => $label)
                                            <option value="{{ $label }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-2 text-right">
                                    <button wire:click="removeMedication({{ $index }})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <button wire:click="addMedication" class="btn btn-secondary btn-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Row
                </button>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                <button x-data @click="$dispatch('close-modal', { name: 'manage-discharge-meds-modal' })" class="btn btn-secondary">Cancel</button>
                <button wire:click="save" class="btn btn-primary flex items-center gap-2">
                    <span wire:loading.remove wire:target="save">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span wire:loading wire:target="save">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                    Save Medications
                </button>
            </div>
        </div>
    </x-modal>
</div>
