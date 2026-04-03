<x-modal name="template-modal" :title="$isEditing ? 'Edit Admission Layout' : 'New Admission Layout'" width="xl">
    <div class="p-8">
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Layout Type</label>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" wire:click="$set('type', 'reason')" 
                            class="px-6 py-4 rounded-2xl border-2 transition-all font-black text-sm uppercase tracking-widest {{ $type == 'reason' ? 'bg-violet-600 border-violet-600 text-white shadow-xl shadow-violet-500/20' : 'bg-gray-50 dark:bg-gray-950 border-transparent text-gray-400' }}">
                        Admission Reason
                    </button>
                    <button type="button" wire:click="$set('type', 'notes')" 
                            class="px-6 py-4 rounded-2xl border-2 transition-all font-black text-sm uppercase tracking-widest {{ $type == 'notes' ? 'bg-emerald-600 border-emerald-600 text-white shadow-xl shadow-emerald-500/20' : 'bg-gray-50 dark:bg-gray-950 border-transparent text-gray-400' }}">
                        Clinical Note
                    </button>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Layout Content</label>
                <input type="text" wire:model="content" placeholder="E.G. ACUTE BRONCHIOLITIS..." class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm placeholder-gray-300 dark:placeholder-gray-700 uppercase tracking-widest">
                @error('content') <p class="text-[10px] font-black text-rose-500 mt-2 ml-1 uppercase">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'template-modal' })" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Discard</button>
                <button type="submit" class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
                    Save Layout
                </button>
            </div>
        </form>
    </div>
</x-modal>
