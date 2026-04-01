<div>
    <x-page-header title="Laboratory Tests" subtitle="Define available pathology tests, reference ranges and pricing models.">
        <x-slot name="actions">
            <button wire:click="$dispatch('create-lab-test')" class="btn btn-primary">

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Test
            </button>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <x-form.input placeholder="Search by test name..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>
                <div class="md:col-span-2">
                    <x-form.select wire:model.live="categoryFilter" name="category_filter">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Code</x-table.th>
                    <x-table.th>Test Name</x-table.th>
                    <x-table.th>Category</x-table.th>
                    <x-table.th>Parameters</x-table.th>
                    <x-table.th>Price</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($labTests as $test)
                    <tr>
                        <td>
                            <span class="text-[10px] font-black font-mono text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-lg italic">
                                {{ $test->code ?: 'N/A' }}
                            </span>
                        </td>

                        <td>
                            <span class="font-bold text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ $test->name }}</span>
                        </td>
                        <td>
                            <x-badge color="violet">{{ $test->category }}</x-badge>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-[10px] font-black text-gray-600 dark:text-gray-400">
                                    {{ $test->parameters_count }} UNIT(S)
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($test->price, 2) }}</span>
                        </td>
                        <td>
                            <x-badge :color="$test->is_active ? 'success' : 'danger'">
                                {{ $test->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="toggleActive({{ $test->id }})" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Toggle Status">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </button>
                                <button wire:click="$dispatch('edit-lab-test', { id: {{ $test->id }} })" 
                                        class="btn btn-ghost px-2 py-2 text-violet-600" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteTest({{ $test->id }})" 
                                        wire:confirm="Delete this test permanently?" 
                                        class="btn btn-ghost px-2 py-2 text-red-500" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No lab tests found configured in the system." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        @if($labTests->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $labTests->links() }}
            </div>
        @endif
    </x-card>

    <livewire:master.lab-form />
</div>
