<div class="space-y-10">
    <x-page-header title="Admission Layouts" subtitle="Manage pediatric admission reasons and common clinical observations to streamline the Pediatrician's workflow.">
        <x-slot name="actions">
            <button @click="$dispatch('create-template')" class="btn btn-primary px-8 py-4 shadow-xl shadow-indigo-500/30 rounded-2xl group transition-all active:scale-95 flex items-center gap-3">
                <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                New Layout
            </button>
        </x-slot>
    </x-page-header>

    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 overflow-hidden">
        <div class="px-8 py-8 border-b border-gray-50 dark:border-gray-800 bg-gray-50/10 dark:bg-gray-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex-1 max-w-md relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-indigo-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="FILTER LAYOUTS..." class="w-full bg-white dark:bg-gray-950 border-none rounded-2xl pl-16 pr-6 py-4 text-xs font-black tracking-widest text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase">
            </div>

            <div class="flex items-center gap-4">
                <select wire:model.live="type" class="bg-white dark:bg-gray-950 border-none rounded-2xl px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm cursor-pointer appearance-none">
                    <option value="">All Types</option>
                    <option value="reason">Admission Reasons</option>
                    <option value="notes">Clinical Notes</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-50 dark:border-gray-800/50">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Layout Type</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Content</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/30">
                    @forelse($templates as $t)
                        <tr class="group hover:bg-gray-50/30 dark:hover:bg-gray-800/40 transition-all duration-300">
                            <td class="px-8 py-5">
                                <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest {{ $t->type == 'reason' ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $t->type == 'reason' ? 'Admission Reason' : 'Clinical Note' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $t->content }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="$dispatch('edit-template', { id: {{ $t->id }} })" class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all active:scale-90">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="delete({{ $t->id }})" wire:confirm="Remove this layout?" class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all active:scale-90">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-8 py-20 text-center flex flex-col items-center justify-center grayscale opacity-30">
                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke-width="1.5" /></svg>
                                <p class="text-xs font-black uppercase tracking-widest text-gray-400">No layouts configured for this module.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="px-8 py-6 border-t border-gray-50 dark:border-gray-800 bg-gray-50/10">
                {{ $templates->links() }}
            </div>
        @endif
    </div>

    <livewire:master.clinical-template-form />
</div>
