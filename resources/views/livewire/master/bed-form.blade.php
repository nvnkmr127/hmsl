<div>
    <x-modal name="bed-modal" :title="$isEditing ? 'Edit Infrastructure' : 'Configure New Bed Asset'" width="md">
        <form wire:submit="save" class="space-y-6">
            <x-form.select label="Parent Ward" wire:model="ward_id" name="ward_id" class="uppercase">
                <option value="">Select Target Ward</option>
                @foreach($wards as $ward)
                    <option value="{{ $ward->id }}">{{ $ward->name }} ({{ $ward->type }})</option>
                @endforeach
            </x-form.select>

            <x-form.input label="Bed Number Identifier" wire:model="bed_number" name="bed_number" placeholder="e.g. B-101 or ICU-01" class="uppercase" />
            
            <div class="py-2">
                <x-form.checkbox label="Ready for Admission (Available)" wire:model="is_available" name="is_available" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'bed-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Asset' : 'Register Bed' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
