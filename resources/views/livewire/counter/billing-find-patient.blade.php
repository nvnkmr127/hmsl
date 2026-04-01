<div class="space-y-4">
    <x-form.input wire:model.live.debounce.300ms="search" placeholder="Search by UHID, name, or phone..." />

    <div class="divide-y divide-gray-100 dark:divide-gray-800 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        @forelse($patients as $patient)
            <button type="button"
                    wire:click="selectPatient({{ $patient->id }})"
                    class="w-full text-left p-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $patient->uhid }}</p>
                        <p class="text-sm font-black text-gray-900 dark:text-white truncate mt-1">{{ $patient->full_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $patient->phone ?? '—' }}</p>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Select</span>
                </div>
            </button>
        @empty
            <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                No patients found.
            </div>
        @endforelse
    </div>
</div>

