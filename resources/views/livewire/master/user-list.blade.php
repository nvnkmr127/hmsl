<div>
    <div class="glass-card overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-4">
            <div class="flex-1 min-w-[300px]">
                <x-form.input 
                    wire:model.live.debounce.350ms="search" 
                    placeholder="Search users by name or email..." 
                    id="user-search"
                />
            </div>
            <button wire:click="$dispatch('create-user')" class="btn btn-primary px-6">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New User
            </button>
        </div>

        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>User Info</x-table.th>
                    <x-table.th>Roles</x-table.th>
                    <x-table.th>Registered Date</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @foreach($user->roles as $role)
                                <x-badge color="indigo">{{ $role->name }}</x-badge>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="$dispatch('edit-user', { id: {{ $user->id }} })" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                @if($user->id !== auth()->id())
                                    <button 
                                        wire:click="delete({{ $user->id }})" 
                                        wire:confirm="Are you sure you want to delete this user?"
                                        class="p-2 text-gray-400 hover:text-rose-600 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colspan="4" message="No users found." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        <div class="px-6 py-4 border-t border-gray-50 dark:border-gray-800">
            {{ $users->links() }}
        </div>
    </div>
</div>
