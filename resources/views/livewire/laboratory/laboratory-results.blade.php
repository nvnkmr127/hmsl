<div>
    <x-page-header title="Test Results" subtitle="Browse, verify, and print completed laboratory reports.">
        <x-slot name="actions">
            <a href="{{ route('laboratory.index') }}" class="btn btn-secondary">Order Queue</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Search Archive">
                <div class="space-y-4">
                    <x-form.input placeholder="Search by patient name or UHID..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    <p class="text-[10px] text-gray-400 font-medium italic">Showing only completed and verified results. Current pendings are in the main queue.</p>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Patient</x-table.th>
                            <x-table.th class="hidden md:table-cell">Test & Date</x-table.th>
                            <x-table.th class="hidden lg:table-cell">Technician</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $r)
                            <tr>
                                <td>
                                    <x-patient-identity :patient="$r->patient" />
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $r->labTest?->name ?? 'Unknown' }}</p>
                                    <p class="text-[10px] text-emerald-600 font-black uppercase tracking-widest">{{ $r->completed_at ? $r->completed_at->format('d M, Y H:i') : 'N/A' }}</p>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-gray-800 flex items-center justify-center text-[10px] font-black text-slate-500 uppercase">
                                            {{ strtoupper(substr($r->technician?->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ $r->technician?->name ?? 'System' }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button class="btn btn-primary px-3 py-1.5 text-xs">
                                             View Report
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="4" message="No completed results found." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($results->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $results->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
