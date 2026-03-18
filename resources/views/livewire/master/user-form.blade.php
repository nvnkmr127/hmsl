<div>
    <x-modal name="user-modal" :title="$isEditing ? 'Edit User' : 'Add New User'" width="xl">
        <form wire:submit="save" class="space-y-6">
            <x-form.input label="Full Name" wire:model="name" placeholder="Enter user's full name" id="user-name" />
            
            <x-form.input type="email" label="Email Address" wire:model="email" placeholder="email@hospital.com" id="user-email" />
            
            <x-form.input type="password" label="Password" wire:model="password" placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'Enter strong password' }}" id="user-password" />
            
            <x-form.select label="User Role" wire:model="role" id="user-role">
                <option value="">Select a role</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ ucfirst(str_replace('_', ' ', $r->name)) }}</option>
                @endforeach
            </x-form.select>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'user-modal' })" class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
