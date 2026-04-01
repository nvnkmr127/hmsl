<div>
    <x-page-header title="Inventory Categories" subtitle="Classify and manage inventory items across departments.">
        <x-slot name="actions">
            <button wire:click="$dispatch('create-category')" class="btn btn-primary">

                Add Category
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <x-form.input placeholder="Search categories..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Category Name</x-table.th>
                    <x-table.th>Description</x-table.th>
                    <x-table.th>Items Count</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $category->name }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-500 line-clamp-1">{{ $category->description ?: 'No description' }}</span>
                        </td>
                        <td>
                             <x-badge color="violet">{{ $category->items_count }} items</x-badge>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="$dispatch('edit-category', { id: {{ $category->id }} })" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteCategory({{ $category->id }})" 
                                        wire:confirm="Permanent delete this category?" 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="4" message="No categories found." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($categories->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $categories->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.inventory-category-form />
</div>
