<div>
    <x-modal name="department-modal" :title="$isEditing ? 'Edit Department' : 'Add New Department'" width="lg">
        <form wire:submit="save" class="space-y-6">
            <x-form.input label="Department Name" wire:model="name" name="name" placeholder="e.g. Cardiology" />
            
            <x-form.textarea label="Description" wire:model="description" name="description" placeholder="Brief details about functions or ward types..." rows="3" />

            <div class="flex items-center space-x-2 py-2">
                <x-form.checkbox label="Active and Visible in System" wire:model="is_active" name="is_active" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'department-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Records' : 'Create Department' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
