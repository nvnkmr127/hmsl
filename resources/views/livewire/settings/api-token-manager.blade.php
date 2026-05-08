<div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header/Create Section -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-[2rem] p-1 shadow-2xl shadow-indigo-500/5">
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative p-8 md:p-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Create API Access</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm max-w-md">Generate a secure token to authenticate your external applications and integrations with the HMS API.</p>
                </div>
                
                <form wire:submit.prevent="createToken" class="flex-1 max-w-xl group">
                    <div class="relative flex items-center p-2 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border-2 border-transparent focus-within:border-indigo-500/20 transition-all duration-300">
                        <div class="absolute left-6 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                        </div>
                        <input type="text" wire:model="tokenName" placeholder="e.g. Mobile App, Custom Dashboard" 
                            class="w-full bg-transparent border-none focus:ring-0 pl-12 pr-4 text-gray-900 dark:text-white font-medium placeholder-gray-400">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2 whitespace-nowrap">
                            <span>Generate</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        </button>
                    </div>
                    @error('tokenName') <span class="text-red-500 text-xs mt-2 ml-4 block font-bold">{{ $message }}</span> @enderror
                </form>
            </div>

            @if($plainTextToken)
                <div class="mt-8 animate-in slide-in-from-top-4 duration-500">
                    <div class="p-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 rounded-3xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-600 text-white animate-pulse">NEW TOKEN</span>
                        </div>
                        <p class="text-indigo-900 dark:text-indigo-300 text-sm font-bold mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Security Alert: Copy this token now. It will not be shown again.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div class="flex-1 w-full p-4 bg-white dark:bg-gray-950 rounded-2xl font-mono text-sm border border-indigo-100 dark:border-gray-800 select-all break-all text-indigo-600 dark:text-indigo-400 font-bold shadow-inner">
                                {{ $plainTextToken }}
                            </div>
                            <button onclick="navigator.clipboard.writeText('{{ $plainTextToken }}'); this.innerHTML='<span class=\'flex items-center gap-2\'>Copied! <svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\' /></svg></span>'; setTimeout(() => this.innerHTML='<span class=\'flex items-center gap-2\'>Copy <svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3\' /></svg></span>', 2000)" 
                                class="w-full sm:w-auto px-6 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                                <span class="flex items-center gap-2">Copy
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Tokens List -->
    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden border border-gray-100 dark:border-gray-700/50">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-wider">Active Tokens</h3>
            <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-3 py-1 rounded-full text-xs font-bold">{{ $tokens->count() }} Total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 dark:text-gray-500 text-[10px] uppercase font-black tracking-[0.2em]">
                        <th class="px-8 py-5">Token Identity</th>
                        <th class="px-8 py-5">Activity Status</th>
                        <th class="px-8 py-5">Created On</th>
                        <th class="px-8 py-5 text-right">Security</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($tokens as $token)
                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-all duration-300">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white text-lg group-hover:text-indigo-600 transition-colors">{{ $token->name }}</div>
                                        <div class="text-xs text-gray-400 font-medium">Sanctum Personal Access Token</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @if($token->last_used_at)
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 text-xs font-black">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            ACTIVE
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $token->last_used_at->diffForHumans() }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-gray-400 dark:text-gray-500 text-xs font-black uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                        NEVER USED
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm text-gray-600 dark:text-gray-300 font-bold">{{ $token->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-gray-400 uppercase font-black tracking-wider">{{ $token->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button wire:click="deleteToken({{ $token->id }})" 
                                    wire:confirm="CRITICAL: This will immediately revoke access for all applications using this token. Proceed?" 
                                    class="relative inline-flex items-center gap-2 text-red-500 hover:text-white hover:bg-red-500 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 overflow-hidden group/btn">
                                    <span class="relative z-10">Revoke Access</span>
                                    <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-300 dark:text-gray-700">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </div>
                                    <p class="text-gray-400 dark:text-gray-500 font-bold text-lg">No active API tokens found</p>
                                    <p class="text-sm text-gray-400 max-w-xs">Create your first token above to start integrating with our secure hospital API.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

