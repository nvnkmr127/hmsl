<div>
    <x-card title="IP Services">
        <x-slot name="action">
            <button x-data @click="$dispatch('open-modal', { name: 'ip-service-modal' }); @this.create()" class="btn btn-primary text-xs flex items-center gap-2 shadow-lg shadow-indigo-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Service
            </button>
        </x-slot>

        <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search services..." class="w-full md:w-1/3 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($services as $service)
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $service->name }}</td>
                            <td class="px-4 py-3">{{ $service->description ?: '-' }}</td>
                            <td class="px-4 py-3 text-right">₹{{ number_format($service->price, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleStatus({{ $service->id }})">
                                    <x-badge :type="$service->is_active ? 'success' : 'danger'">
                                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button x-data @click="$dispatch('open-modal', { name: 'ip-service-modal' }); @this.edit({{ $service->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button wire:click="delete({{ $service->id }})" wire:confirm="Are you sure you want to delete this IP service?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No IP services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $services->links() }}
        </div>
    </x-card>

    <!-- Modal -->
    <x-modal name="ip-service-modal" title="{{ $isEditing ? 'Edit IP Service' : 'Add New IP Service' }}">
        <div class="p-6 space-y-6">
            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/30 mb-6">
                <p class="text-sm text-indigo-800 dark:text-indigo-300">
                    {{ $isEditing ? 'Update the details for this IP service below.' : 'Create a new IP service for billing and discharge processes.' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs uppercase tracking-wider font-bold text-gray-500 dark:text-gray-400 mb-2">Service Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Nursing Charges" class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-colors">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs uppercase tracking-wider font-bold text-gray-500 dark:text-gray-400 mb-2">Price (₹) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" wire:model="price" step="0.01" min="0" placeholder="0.00" class="w-full pl-8 text-sm rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-colors">
                    </div>
                    @error('price') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs uppercase tracking-wider font-bold text-gray-500 dark:text-gray-400 mb-2">Description <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <textarea wire:model="description" rows="3" placeholder="Brief details about the service..." class="w-full text-sm rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-colors"></textarea>
                    @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 flex items-center bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center h-5">
                        <input type="checkbox" wire:model="is_active" id="isActive" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition-colors">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="isActive" class="font-bold text-gray-700 dark:text-gray-300">Active Service</label>
                        <p class="text-gray-500 text-xs">Inactive services will not be visible during discharge billing.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'ip-service-modal' })" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="save" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg shadow-indigo-500/30 transition-all flex items-center gap-2">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="save">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    Save Service
                </button>
            </div>
        </div>
    </x-modal>
</div>
