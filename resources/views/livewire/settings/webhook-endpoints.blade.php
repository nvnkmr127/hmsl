<div class="space-y-6">
    <!-- Tabs Header -->
    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
        <div class="flex space-x-8">
            <button wire:click="$set('activeTab', 'outbound')" 
                    class="pb-4 text-sm font-black uppercase tracking-[0.2em] transition-all relative {{ $activeTab === 'outbound' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                Outgoing Webhooks
                @if($activeTab === 'outbound') <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-600 rounded-t-full"></div> @endif
            </button>
            <button wire:click="$set('activeTab', 'inbound')" 
                    class="pb-4 text-sm font-black uppercase tracking-[0.2em] transition-all relative {{ $activeTab === 'inbound' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                Incoming Sources
                @if($activeTab === 'inbound') <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-600 rounded-t-full"></div> @endif
            </button>
        </div>
        <button wire:click="openModal(null, '{{ $activeTab }}')" class="btn-primary px-6 py-2.5 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span class="text-xs font-black uppercase tracking-widest">{{ $activeTab === 'outbound' ? 'Add Endpoint' : 'Add Source' }}</span>
        </button>
    </div>

    <!-- Outbound Content -->
    @if($activeTab === 'outbound')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($endpoints as $ep)
            <div class="bg-white dark:bg-gray-800 rounded-mega border border-gray-100 dark:border-gray-700/50 p-8 relative overflow-hidden group shadow-sm transition-all hover:shadow-xl hover:shadow-indigo-500/5">
                @if(!$ep->is_active)
                    <div class="absolute inset-0 bg-gray-50/80 dark:bg-gray-900/80 z-10 flex items-center justify-center backdrop-blur-[2px]">
                        <span class="bg-gray-800 text-white text-tiny font-black px-4 py-1.5 rounded-full uppercase">Suspended</span>
                    </div>
                @endif

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ $ep->name }}</h3>
                        <p class="text-tiny text-gray-400 font-bold truncate max-w-[200px]">{{ $ep->url }}</p>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button wire:click="openModal({{ $ep->id }}, 'outbound')" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
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
                    <button wire:click="toggleStatus({{ $ep->id }}, 'outbound')" class="text-tiny font-black uppercase tracking-widest {{ $ep->is_active ? 'text-amber-500' : 'text-emerald-500' }}">
                        {{ $ep->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button 
                        wire:confirm="Permanent deletion of this endpoint?"
                        wire:click="delete({{ $ep->id }}, 'outbound')" 
                        class="text-tiny font-black text-rose-500 uppercase tracking-widest"
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
    @endif

    <!-- Inbound Content -->
    @if($activeTab === 'inbound')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($sources as $src)
            <div class="bg-white dark:bg-gray-800 rounded-mega border border-gray-100 dark:border-gray-700/50 p-8 relative overflow-hidden group shadow-sm transition-all hover:shadow-xl hover:shadow-indigo-500/5">
                @if(!$src->is_active)
                    <div class="absolute inset-0 bg-gray-50/80 dark:bg-gray-900/80 z-10 flex items-center justify-center backdrop-blur-[2px]">
                        <span class="bg-gray-800 text-white text-tiny font-black px-4 py-1.5 rounded-full uppercase">Inactive</span>
                    </div>
                @endif

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ $src->name }}</h3>
                            <span class="text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter {{ $src->auth_type === 'open' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }}">
                                {{ $src->auth_type }}
                            </span>
                        </div>
                        <p class="text-tiny text-indigo-600 font-black tracking-widest uppercase">/api/v1/webhooks/{{ $src->slug }}</p>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button wire:click="openModal({{ $src->id }}, 'inbound')" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 mt-4">
                    <p class="text-[10px] text-gray-500 font-medium leading-relaxed italic">
                        Configure your external service to send POST requests to the endpoint URL above.
                    </p>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-700/50 flex items-center justify-between">
                    <button wire:click="toggleStatus({{ $src->id }}, 'inbound')" class="text-tiny font-black uppercase tracking-widest {{ $src->is_active ? 'text-amber-500' : 'text-emerald-500' }}">
                        {{ $src->is_active ? 'Disable' : 'Enable' }}
                    </button>
                    <button 
                        wire:confirm="Permanent deletion of this source?"
                        wire:click="delete({{ $src->id }}, 'inbound')" 
                        class="text-tiny font-black text-rose-500 uppercase tracking-widest"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-gray-50 dark:bg-gray-800/40 rounded-[3rem] border-4 border-dashed border-gray-100 dark:border-gray-700/50">
                <p class="text-gray-400 text-sm italic font-medium">No inbound sources defined.</p>
            </div>
        @endforelse
    </div>
    @endif

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
                <div class="px-10 py-8 bg-indigo-600 text-white">
                    <h2 class="text-2xl font-black uppercase tracking-tight">
                        {{ $activeTab === 'outbound' ? ($editingEndpointId ? 'Edit Endpoint' : 'New Outbound Integration') : ($editingSourceId ? 'Edit Source' : 'New Inbound Source') }}
                    </h2>
                    <p class="text-xs text-indigo-100 mt-1 uppercase tracking-widest font-bold">
                        {{ $activeTab === 'outbound' ? 'Connect HMS to external APIs' : 'Receive data from external services' }}
                    </p>
                </div>
                
                <form wire:submit="save" class="p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input label="Display Name" wire:model="name" placeholder="e.g. CRM Sync or Stripe Inbound" />
                        
                        @if($activeTab === 'outbound')
                            <x-form.input label="Target URL" wire:model="url" placeholder="https://api.yourcrm.com/v1/hms-hook" />
                        @else
                            <x-form.input label="Endpoint Slug" wire:model="slug" placeholder="e.g. stripe-payments" />
                        @endif
                    </div>

                    @if($activeTab === 'inbound')
                    <div class="space-y-4">
                        <label class="text-tiny font-black text-gray-400 uppercase tracking-widest block">Authentication Type</label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach(['secret' => 'HMAC Secret', 'bearer' => 'Bearer Token', 'open' => 'No Auth (Open)'] as $val => $lbl)
                                <label class="flex flex-col items-center justify-center gap-2 p-4 rounded-3xl border-2 transition-all cursor-pointer {{ $authType === $val ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-100 dark:border-gray-700/50 hover:border-indigo-200' }}">
                                    <input type="radio" wire:model.live="authType" value="{{ $val }}" class="sr-only">
                                    <span class="text-[10px] font-black uppercase tracking-tight text-center">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($activeTab === 'outbound')
                    <div class="space-y-4">
                        <label class="text-tiny font-black text-gray-400 uppercase tracking-widest block">Subscribe to Events</label>
                        <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-900/40 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50">
                            @foreach($availableEvents as $key => $label)
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" wire:model="selectedEvents" value="{{ $key }}" class="w-4 h-4 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($authType !== 'open' || $activeTab === 'outbound')
                    <div class="p-6 bg-amber-50 dark:bg-amber-900/20 rounded-3xl border border-amber-100 dark:border-amber-800/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-tiny font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">
                                {{ $authType === 'secret' || $activeTab === 'outbound' ? 'Secret Signing Key (HMAC)' : 'Bearer Token' }}
                            </span>
                            <button type="button" wire:click="$set('secret', '{{ Str::random(32) }}')" class="text-tiny font-black text-indigo-600 uppercase underline">Regenerate</button>
                        </div>
                        <code class="text-xs font-mono text-gray-600 dark:text-gray-400 break-all select-all">{{ $secret }}</code>
                        <p class="text-[8px] text-amber-600 mt-3 font-bold uppercase tracking-widest">
                            {{ $authType === 'secret' || $activeTab === 'outbound' ? 'Used to sign and verify every request via HMAC-SHA256.' : 'Must be sent in the Authorization header as Bearer token.' }}
                        </p>
                    </div>
                    @endif

                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50 flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-6 py-2.5 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Cancel</button>
                        <button type="submit" class="btn-primary px-10 py-2.5 text-xs font-black uppercase tracking-widest">
                            {{ ($editingEndpointId || $editingSourceId) ? 'Update Config' : 'Create Config' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
