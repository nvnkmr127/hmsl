<div>
    <div class="flex justify-between items-center mb-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Discharge Notes (General)</h4>
        @if(!$isEditing)
            <button wire:click="$set('isEditing', true)" class="text-xs text-indigo-500 hover:text-indigo-600 font-semibold flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit
            </button>
        @endif
    </div>

    @if($isEditing)
        <div class="space-y-3 mt-2">
            <textarea wire:model="notes" rows="4" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter discharge notes here..."></textarea>
            <div class="flex gap-2">
                <button wire:click="save" class="btn btn-primary btn-sm">Save Note</button>
                <button wire:click="$set('isEditing', false)" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
        </div>
    @else
        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line mt-1">{{ $notes ?: '—' }}</p>
    @endif
</div>
