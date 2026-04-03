<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Create New API Token</h3>
        
        <form wire:submit.prevent="createToken" class="flex space-x-4">
            <div class="flex-1">
                <input type="text" wire:model="tokenName" placeholder="e.g. Mobile App, External Integrator" 
                    class="w-full h-14 px-6 rounded-2xl bg-gray-50 dark:bg-gray-900 border-none focus:ring-2 focus:ring-indigo-500/20 transition-all text-gray-800 dark:text-white placeholder-gray-400">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-bold transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                <span>Generate Token</span>
            </button>
        </form>

        @if($plainTextToken)
            <div class="mt-8 p-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-2xl">
                <p class="text-amber-800 dark:text-amber-400 text-sm font-bold mb-2">IMPORTANT: Copy your new API token now. You won't be able to see it again!</p>
                <div class="flex items-center space-x-3">
                    <code class="flex-1 p-3 bg-white dark:bg-gray-900 rounded-xl text-indigo-600 dark:text-indigo-400 font-mono text-sm border border-amber-200/50">
                        {{ $plainTextToken }}
                    </code>
                    <button onclick="navigator.clipboard.writeText('{{ $plainTextToken }}')" class="p-3 bg-white dark:bg-gray-900 rounded-xl text-gray-400 hover:text-indigo-600 border border-amber-200/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight">Active API Tokens</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-8 py-4">Name</th>
                        <th class="px-8 py-4">Last Used</th>
                        <th class="px-8 py-4">Created</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($tokens as $token)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-8 py-6">
                                <span class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $token->name }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-sm text-gray-400">
                                {{ $token->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button wire:click="deleteToken({{ $token->id }})" wire:confirm="Are you sure you want to revoke this token?" 
                                    class="text-red-500 hover:text-red-700 font-bold text-sm tracking-tight">Revoke Access</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-gray-400">No active API tokens found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
