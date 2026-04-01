<div>
    <x-modal name="category-modal" :title="$isEditing ? 'Edit Category' : 'New Category'" width="xl">
        <form wire:submit="save" class="space-y-6">
            
            <div class="space-y-5">
                <x-form.input label="Category Name" wire:model="name" name="name" placeholder="e.g. Consumables, Lab Reagents" />
                <x-form.textarea label="Description" wire:model="description" name="description" placeholder="Briefly describe the usage of this category..." rows="3" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'category-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Category' : 'Save Category' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
