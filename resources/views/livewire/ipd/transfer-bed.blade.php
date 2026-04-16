<div>
    <button wire:click="$set('showModal', true)" class="btn btn-secondary text-xs">
        Transfer Ward
    </button>

    @if($showModal)
        <x-modal name="transfer-bed-modal" title="Transfer Patient to Another Ward/Bed">
            <div class="p-4 space-y-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Current: <span class="font-bold">{{ $admission->bed?->ward?->name ?? 'N/A' }} / {{ $admission->bed?->bed_number ?? 'N/A' }}</span>
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

                @if($newWardId)
                    <div>
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Select Bed</label>
                        <select wire:model="newBedId" class="w-full rounded-xl border-gray-200 dark:border-gray-700">
                            <option value="">Select Bed</option>
                            @foreach($this->availableBeds as $bed)
                                <option value="{{ $bed->id }}">
                                    {{ $bed->bed_number }}
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
                @endif

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button @click="$dispatch('close-modal', { name: 'transfer-bed-modal' })" class="btn btn-secondary">Cancel</button>
                    <button wire:click="transfer" class="btn btn-primary" @unless($newBedId) disabled @endunless>Transfer</button>
                </div>
            </div>
        </x-modal>
    @endif
</div>
