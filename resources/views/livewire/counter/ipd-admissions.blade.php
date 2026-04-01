<div>
    <x-page-header title="IPD Admissions" subtitle="Monitor and manage inpatient admissions, ward occupancy and discharges.">
        <x-slot name="actions">
            <a href="{{ route('counter.ipd.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Admission
            </a>
        </x-slot>
    </x-page-header>

    <x-card :noPad="true">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="max-w-md w-full">
                <x-form.input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by UHID, Name or Admission #..." 
                    icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
                </div>
                <label class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                    <input type="checkbox" wire:model.live="showDischarged" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500/30">
                    Show discharged
                </label>
            </div>
        </div>

        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($admissions as $adm)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-black text-gray-900 dark:text-white truncate">{{ $adm->patient->full_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $adm->patient->uhid }} · {{ $adm->admission_number }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $adm->bed?->ward?->name ?? 'N/A' }} · BED: {{ $adm->bed?->bed_number ?? '—' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $adm->admission_date->format('d M, Y H:i') }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            @php 
                                $statusCls = match($adm->status) {
                                    'Admitted' => 'violet',
                                    'Discharged' => 'success',
                                    default => 'warning'
                                };
                            @endphp
                            <x-badge :color="$statusCls">{{ $adm->status }}</x-badge>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2 justify-end">
                        @if($adm->status === 'Admitted')
                            <button wire:click="dischargePatient({{ $adm->id }})"
                                    class="btn btn-secondary px-3 py-2 text-xs">
                                Discharge
                            </button>
                        @else
                            <a href="{{ route('discharge.summary', ['admission' => $adm->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Summary</a>
                            <a target="_blank" href="{{ route('discharge.print', ['admission' => $adm->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                        @endif
                        <a href="{{ route('counter.patients.history', $adm->patient_id) }}" class="btn btn-ghost px-3 py-2 text-xs">History</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No admissions found.</div>
            @endforelse
        </div>

        <div class="hidden md:block">
        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Patient Details</x-table.th>
                    <x-table.th>Admission Info</x-table.th>
                    <x-table.th>Ward & Bed</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($admissions as $adm)
                    <tr>
                        <td>
                            <div class="flex flex-col min-w-0">
                                <span class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-tight truncate">{{ $adm->patient->full_name }}</span>
                                <span class="text-[10px] text-violet-600 dark:text-violet-400 font-bold tracking-widest">{{ $adm->patient->uhid }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 dark:text-gray-200 text-xs">{{ $adm->admission_number }}</span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold mt-0.5">{{ $adm->admission_date->format('d M, Y H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center text-violet-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">{{ $adm->bed?->ward?->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] font-bold text-violet-500 uppercase">BED: {{ $adm->bed?->bed_number ?? '—' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php 
                                $statusCls = match($adm->status) {
                                    'Admitted' => 'violet',
                                    'Discharged' => 'success',
                                    default => 'warning'
                                };
                            @endphp
                            <x-badge :color="$statusCls">{{ $adm->status }}</x-badge>
                        </td>
                        <td>
                            <div class="flex justify-end gap-1">
                               @if($adm->status === 'Admitted')
                                    <button 
                                        wire:click="dischargePatient({{ $adm->id }})"
                                        class="btn btn-outline btn-sm py-1.5 h-auto text-red-600 border-red-200 hover:bg-red-50 text-[10px]"
                                    >
                                        Discharge Patient
                                    </button>
                                @else
                                    <a href="{{ route('discharge.summary', ['admission' => $adm->id]) }}" class="btn btn-outline btn-sm py-1.5 h-auto text-[10px]">
                                        Summary
                                    </a>
                                    <a target="_blank" href="{{ route('discharge.print', ['admission' => $adm->id]) }}" class="btn btn-outline btn-sm py-1.5 h-auto text-[10px]">
                                        Print
                                    </a>
                                @endif
                                <a href="{{ route('counter.patients.history', $adm->patient_id) }}" class="btn btn-ghost px-2 py-2" title="View History">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colSpan="6" message="No active IPD admissions found." />
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

    <x-modal name="ipd-discharge-modal" title="Discharge Patient">
        <div class="space-y-4">
            <div>
                <label class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Discharge Notes (Optional)</label>
                <textarea wire:model="dischargeNotes" rows="4" class="mt-2 w-full bg-gray-50 dark:bg-gray-900/50 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-semibold placeholder-gray-300 dark:placeholder-gray-700" placeholder="Discharge instructions or summary notes..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', { name: 'ipd-discharge-modal' })" class="btn btn-ghost">Cancel</button>
                <button type="button" wire:click="confirmDischarge" class="btn btn-primary">Confirm Discharge</button>
            </div>
        </div>
    </x-modal>
</div>
