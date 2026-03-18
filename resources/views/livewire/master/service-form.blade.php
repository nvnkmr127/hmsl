<div>
    <x-modal name="service-modal" :title="$isEditing ? 'Edit Service' : 'Configure New Service'" width="lg">
        <form wire:submit="save" class="space-y-6">
            <x-form.input label="Service Name" wire:model="name" name="name" placeholder="e.g. ECG - 12 Lead" />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.select label="Service Category" wire:model="category" name="category">
                    <option value="">Select Category</option>
                    <option value="OPD">OPD Consultation</option>
                    <option value="IPD">IPD Service</option>
                    <option value="LAB">Laboratory</option>
                    <option value="RADIO">Radiology</option>
                    <option value="SURGERY">Surgery</option>
                    <option value="OTHERS">Others</option>
                </x-form.select>

                <x-form.input label="Base Price (₹)" wire:model="price" name="price" type="number" step="1" />
            </div>
            
            <x-form.textarea label="Service Description" wire:model="description" name="description" placeholder="Notes or instructions for billing..." rows="3" />

            <div class="py-2">
                <x-form.checkbox label="Active / Available for Billing" wire:model="is_active" name="is_active" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'service-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Configuration' : 'Save Service' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
