<div>
    <x-page-header title="Discharge Management" subtitle="Process patient discharges, update bed availability, and add discharge notes.">
        <x-slot name="actions">
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary">Admissions</a>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input placeholder="Search admitted patients by name or UHID..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </div>
        </div>

        <div wire:loading.flex wire:target="search,selectForDischarge,processDischarge" class="items-center justify-center p-6 text-xs font-black uppercase tracking-widest text-gray-400">
            Loading discharge list...
        </div>

        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800" wire:loading.remove wire:target="search,selectForDischarge,processDischarge">
            @forelse($admissions as $admission)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-black text-gray-900 dark:text-white text-sm truncate">{{ $admission->patient->full_name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $admission->admission_number }} · {{ $admission->patient->uhid }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                {{ $admission->bed?->ward?->name ?? 'N/A' }} · {{ $admission->bed?->name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $admission->admission_date->format('d M, Y H:i') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 truncate">
                                {{ $admission->doctor?->full_name ?? 'Unassigned' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <button wire:click="selectForDischarge({{ $admission->id }})"
                                    wire:loading.attr="disabled" wire:target="selectForDischarge({{ $admission->id }})"
                                    class="btn btn-primary px-3 py-2 text-xs">
                                <span wire:loading.remove wire:target="selectForDischarge({{ $admission->id }})">Discharge</span>
                                <span wire:loading wire:target="selectForDischarge({{ $admission->id }})">...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                    No active admissions found.
                </div>
            @endforelse
        </div>

        <div class="hidden md:block">
        <x-table.wrapper wire:loading.remove wire:target="search,selectForDischarge,processDischarge">
            <thead>
                <tr>
                    <x-table.th>Patient</x-table.th>
                    <x-table.th>Location</x-table.th>
                    <x-table.th>Admission Date</x-table.th>
                    <x-table.th>Doctor</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($admissions as $admission)
                    <tr>
                        <td>
                            <x-patient-identity :patient="$admission->patient" :subtitle="$admission->admission_number" />
                        </td>
                        <td>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $admission->bed?->ward?->name ?? 'N/A' }}</p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-widest">{{ $admission->bed?->name ?? 'N/A' }}</p>
                        </td>
                        <td>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $admission->admission_date->format('d M, Y') }}</p>
                            <p class="text-[10px] text-gray-500">{{ $admission->admission_date->format('H:i') }}</p>
                        </td>
                        <td>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $admission->doctor?->full_name ?? 'Unassigned' }}</p>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="selectForDischarge({{ $admission->id }})" 
                                        wire:loading.attr="disabled" wire:target="selectForDischarge({{ $admission->id }})"
                                        class="btn btn-primary px-3 py-1.5 text-xs">
                                    <span wire:loading.remove wire:target="selectForDischarge({{ $admission->id }})">Discharge</span>
                                    <span wire:loading wire:target="selectForDischarge({{ $admission->id }})">Opening...</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="5" message="No active admissions found matching your criteria..." />
                @endforelse
            </tbody>
        </x-table.wrapper>
        </div>

        @if($admissions->hasPages())
            <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                {{ $admissions->links() }}
            </div>
        @endif
    </x-card>

    <!-- Discharge Modal -->
    <x-modal name="discharge-modal" title="Confirm Patient Discharge">
        <form wire:submit.prevent="processDischarge">
            <div class="space-y-4">
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900 text-amber-800 dark:text-amber-200">
                    <p class="text-xs font-black uppercase tracking-widest mb-1 italic">Important Note</p>
                    <p class="text-xs leading-relaxed">Discharging the patient will immediately free the bed for new admissions. Ensure all clinical paperwork and final billing are complete before confirming.</p>
                </div>

                <div>
                    <label for="discharge-notes" class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Final Discharge Notes</label>
                    <x-form.textarea id="discharge-notes" wire:model="dischargeNotes" placeholder="Summary of treatment, medication advice, or follow-up instructions..." rows="4" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'discharge-modal' })" class="btn btn-ghost">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="processDischarge" class="btn btn-primary">
                    <span wire:loading.remove wire:target="processDischarge">Process Discharge</span>
                    <span wire:loading wire:target="processDischarge">Processing...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
