<div>
    <x-modal name="lab-order-modal" title="Order Lab Tests" width="3xl">
        <form wire:submit="save" class="space-y-4">
            <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
                @foreach($tests as $t)
                    <label class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/20">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" wire:model="selectedTests" value="{{ $t->id }}" class="checkbox checkbox-sm" />
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $t->name }}</p>
                                <p class="text-xs text-gray-500">₹ {{ number_format((float) $t->price, 2) }}</p>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div>
                <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1.5 pl-1">Notes (Optional)</label>
                <textarea wire:model="notes" rows="2"
                    class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 px-4 py-3 sm:text-sm resize-none transition-all duration-300"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="$dispatch('close-modal', { name: 'lab-order-modal' })" class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    Create Order
                </button>
            </div>
        </form>
    </x-modal>
</div>

