<div>
    <button x-data @click="$dispatch('open-modal', { name: 'transfer-bed-modal' })"
        class="p-3 bg-blue-50 dark:bg-blue-950/30 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-blue-500/20 flex items-center gap-2"
        title="Transfer Ward">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        <span class="text-xs font-bold uppercase tracking-wide">Transfer Ward</span>
    </button>
    <x-modal name="transfer-bed-modal" title="Transfer Patient to Another Ward/Bed">
        <div class="p-4 space-y-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Current: <span class="font-bold">{{ $admission->bed?->ward?->name ?? 'N/A' }} /
                        {{ $admission->bed?->bed_number ?? 'N/A' }}</span>
                </p>
            </div>

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Select Ward</label>
                <select wire:model.live="newWardId" class="w-full rounded-xl border-gray-200 dark:border-gray-700">
                    <option value="">Select Ward</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>

            @php
                $selectedWardObj = collect($wards)->first(function($w) use ($newWardId) { return $w->id == $newWardId; });
                $isIcu = $selectedWardObj && in_array(trim(strtoupper($selectedWardObj->name)), ['NICU', 'PICU']);
            @endphp

            @if($newWardId && !$isIcu)
                <div>
                    <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Select Bed</label>
                    <select wire:model.live="newBedId" class="w-full rounded-xl border-gray-200 dark:border-gray-700">
                        <option value="">Select Bed</option>
                        @foreach($this->availableBeds as $bed)
                            <option value="{{ $bed->id }}">
                                {{ strtoupper($bed->bed_number) }}
                                @if(!$bed->is_available && $bed->id === $admission->bed_id)
                                    (Current)
                                @elseif(!$bed->is_available)
                                    (Occupied)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('newBedId') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
            @elseif($newWardId && $isIcu)
                <div class="py-4 bg-emerald-50 dark:bg-emerald-950 rounded-xl border border-emerald-100 dark:border-emerald-800 text-center">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Internal bed will be auto-assigned for {{ $selectedWardObj->name }}</p>
                </div>
            @endif

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Reason (Optional)</label>
                <textarea wire:model="reason"
                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-blue-500 focus:border-blue-500"
                    rows="2" placeholder="Reason for transfer..."></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button wire:click="cancelTransfer" class="btn btn-secondary">Cancel</button>
                <button wire:click="transfer" class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                    @unless($newBedId || $isIcu) disabled @endunless>Transfer</button>
            </div>
        </div>
    </x-modal>
</div>