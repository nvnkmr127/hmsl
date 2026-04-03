<div>
    <x-modal name="lab-modal" :title="$isEditing ? 'Update Lab Test' : 'Configure New Test'" width="4xl">
        <form wire:submit="save" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-5">
                    <p class="section-lbl" style="color:#7c3aed">Basic details</p>
                    <x-form.input label="Internal Code (e.g. CBC01)" wire:model="code" name="code" placeholder="e.g. CBC01" />
                    <x-form.input label="Test Name" wire:model="name" name="name" placeholder="e.g. Complete Blood Count (CBC)" />

                    <x-form.textarea label="Clinical Notes / Preparation" wire:model="description" name="description" placeholder="Any special instructions for the patient..." rows="3" />
                </div>
                <div class="space-y-5">
                    <p class="section-lbl" style="color:#7c3aed">Category & Price</p>
                    <x-form.select label="Laboratory Section" wire:model="category" name="category">
                        <option value="">Select Category</option>
                        <option value="Pathology">Pathology</option>
                        <option value="Biochemistry">Biochemistry</option>
                        <option value="Hematology">Hematology</option>
                        <option value="Microbiology">Microbiology</option>
                        <option value="Others">Others</option>
                    </x-form.select>
                    <x-form.input label="Report Price (₹)" wire:model="price" name="price" type="number" step="1" />
                    <div class="pt-2">
                        <x-form.checkbox label="Make Available for Billing" wire:model="is_active" name="is_active" />
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                    <p class="section-lbl" style="color:#7c3aed">Measured Parameters</p>
                    <button type="button" wire:click="addParameter" class="btn btn-outline btn-sm py-1.5 h-auto text-tiny uppercase">
                        + Add Parameter Row
                    </button>
                </div>

                <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                    @forelse($parameters as $index => $param)
                        <div class="flex items-end gap-3 bg-gray-50/50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                            <div class="flex-1">
                                <x-form.input label="Param Name" wire:model="parameters.{{ $index }}.name" placeholder="e.g. Hemoglobin" />
                            </div>
                            <div class="w-24">
                                <x-form.input label="Unit" wire:model="parameters.{{ $index }}.unit" placeholder="e.g. g/dL" />
                            </div>
                            <div class="w-40">
                                <x-form.input label="Ref. Range" wire:model="parameters.{{ $index }}.reference_range" placeholder="e.g. 13.5 - 17.5" />
                            </div>
                            <button type="button" wire:click="removeParameter({{ $index }})" 
                                    class="p-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors mb-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-10 bg-gray-50/50 dark:bg-gray-950/20 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest italic">No parameters defined for this test.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'lab-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Configuration' : 'Save Test' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
