<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Outgoing Webhooks</h2>
            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-1">Configure external system integrations</p>
        </div>
        <button wire:click="openModal()" class="btn-primary px-6 py-2.5 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span class="text-xs font-black uppercase tracking-widest">Add Endpoint</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($endpoints as $ep)
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 p-8 relative overflow-hidden group shadow-sm">
                @if(!$ep->is_active)
                    <div class="absolute inset-0 bg-gray-50/80 dark:bg-gray-900/80 z-10 flex items-center justify-center backdrop-blur-[2px]">
                        <span class="bg-gray-800 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase">Suspended</span>
                    </div>
                @endif

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ $ep->name }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold truncate max-w-[200px]">{{ $ep->url }}</p>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button wire:click="openModal({{ $ep->id }})" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($ep->events as $event)
                            <span class="text-[8px] font-black bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-2 py-1 rounded-lg uppercase tracking-widest">
                                {{ $availableEvents[$event] ?? $event }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-700/50 flex items-center justify-between">
                    <button wire:click="toggleStatus({{ $ep->id }})" class="text-[10px] font-black uppercase tracking-widest {{ $ep->is_active ? 'text-amber-500' : 'text-emerald-500' }}">
                        {{ $ep->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button 
                        wire:confirm="Permanent deletion of this endpoint?"
                        wire:click="delete({{ $ep->id }})" 
                        class="text-[10px] font-black text-rose-500 uppercase tracking-widest"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-gray-50 dark:bg-gray-800/40 rounded-[3rem] border-4 border-dashed border-gray-100 dark:border-gray-700/50">
                <p class="text-gray-400 text-sm italic font-medium">No webhook endpoints configured yet.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
                <div class="px-10 py-8 bg-indigo-600 text-white">
                    <h2 class="text-2xl font-black uppercase tracking-tight">{{ $editingEndpointId ? 'Edit Endpoint' : 'Configure New Integration' }}</h2>
                    <p class="text-xs text-indigo-100 mt-1 uppercase tracking-widest font-bold">Connect HMS to external APIs</p>
                </div>
                
                <form wire:submit="save" class="p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input label="Integration Name" wire:model="name" placeholder="e.g. CRM Sync" />
                        <x-form.input label="Target URL" wire:model="url" placeholder="https://api.yourcrm.com/v1/hms-hook" />
                    </div>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Subscribe to Events</label>
                        <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-900/40 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50">
                            @foreach($availableEvents as $key => $label)
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" wire:model="selectedEvents" value="{{ $key }}" class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-6 bg-amber-50 dark:bg-amber-900/20 rounded-3xl border border-amber-100 dark:border-amber-800/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">Secret Signing Key</span>
                            <button type="button" wire:click="$set('secret', '{{ Str::random(32) }}')" class="text-[10px] font-black text-indigo-600 uppercase underline">Regenerate</button>
                        </div>
                        <code class="text-xs font-mono text-gray-600 dark:text-gray-400 break-all select-all">{{ $secret }}</code>
                        <p class="text-[8px] text-amber-600 mt-3 font-bold uppercase tracking-widest">Keep this secret. Used to sign every request via HMAC-SHA256.</p>
                    </div>

                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50 flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-6 py-2.5 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Cancel</button>
                        <button type="submit" class="btn-primary px-10 py-2.5 text-xs font-black uppercase tracking-widest">
                            {{ $editingEndpointId ? 'Update Config' : 'Create Integration' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
