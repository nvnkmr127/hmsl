<div>
    <x-modal name="ward-modal" :title="$isEditing ? 'Update Ward Facility' : 'Configure New Ward'" width="2xl">
        <form wire:submit="save" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input label="Ward / Wing Name" wire:model="name" name="name" placeholder="e.g. ICU Wing A" />
                
                <x-form.select label="Ward Classification" wire:model="type" name="type">
                    <option value="">Select Category</option>
                    <option value="General">General Ward</option>
                    <option value="Semi-Private">Semi-Private</option>
                    <option value="Private">Private Room</option>
                    <option value="ICU">ICU (Intensive Care)</option>
                    <option value="CCU">CCU (Cardiac Care)</option>
                    <option value="ER">Emergency / Recovery</option>
                </x-form.select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input label="Daily Occupancy Charge (₹)" wire:model="daily_charge" name="daily_charge" type="number" step="1" />
                <x-form.input label="Total Bed Capacity" wire:model="capacity" name="capacity" type="number" placeholder="Number of beds" />
            </div>

            <div class="p-4 bg-amber-50 dark:bg-amber-950/20 rounded-xl border border-amber-100 dark:border-amber-900/30">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-xs font-medium text-amber-800 dark:text-amber-200 leading-relaxed italic">
                        Changing capacity will automatically sync beds in the inventory. Occupied beds cannot be removed until discharged.
                    </p>
                </div>
            </div>

            <div class="py-2">
                <x-form.checkbox label="Make Ward Operational" wire:model="is_active" name="is_active" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'ward-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Configuration' : 'Create Ward' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
