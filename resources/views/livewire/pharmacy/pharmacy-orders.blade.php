<div>
    <x-page-header title="Pharmacy Orders" subtitle="Dispense medicines for patient prescriptions and track order statuses.">
        <x-slot name="actions">
            <a href="{{ route('pharmacy.stock') }}" class="btn btn-secondary">Stock</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Filters">
                <div class="space-y-4">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Order Status</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $status === 'pending' ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900' : 'border-gray-100 dark:border-gray-800' }}">
                            <input type="radio" wire:model.live="status" value="pending" class="text-violet-600 focus:ring-violet-500">
                            <span class="text-xs font-bold uppercase tracking-widest {{ $status === 'pending' ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">Pending Orders</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $status === 'dispensed' ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900' : 'border-gray-100 dark:border-gray-800' }}">
                            <input type="radio" wire:model.live="status" value="dispensed" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold uppercase tracking-widest {{ $status === 'dispensed' ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-500' }}">Dispensed</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $status === 'all' ? 'bg-blue-50 border-blue-200 dark:bg-blue-950/20 dark:border-blue-900' : 'border-gray-100 dark:border-gray-800' }}">
                            <input type="radio" wire:model.live="status" value="all" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-bold uppercase tracking-widest {{ $status === 'all' ? 'text-blue-700 dark:text-blue-400' : 'text-gray-500' }}">All History</span>
                        </label>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
                    <x-form.input placeholder="Search by patient name or UHID..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>

                <div wire:loading.flex wire:target="search,status,dispense" class="items-center justify-center p-6 text-xs font-black uppercase tracking-widest text-gray-400">
                    Loading pharmacy orders...
                </div>

                <x-table.wrapper wire:loading.remove wire:target="search,status,dispense">
                    <thead>
                        <tr>
                            <x-table.th>Patient</x-table.th>
                            <x-table.th class="hidden md:table-cell">Doctor / Date</x-table.th>
                            <x-table.th class="hidden lg:table-cell">Medicines</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $p)
                            <tr>
                                <td>
                                    <x-patient-identity :patient="$p->patient" />
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $p->doctor?->full_name ?? 'Unassigned' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $p->created_at->format('d M, Y H:i') }}</p>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <div class="space-y-1">
                                        @foreach(($p->medicines ?? []) as $m)
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-1.5 rounded-full bg-violet-400"></div>
                                                <p class="text-xs text-gray-700 dark:text-gray-300">{{ $m['name'] ?? 'Medicine' }} <span class="text-[10px] text-gray-400">({{ $m['dose'] ?? 'N/A' }})</span></p>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <x-badge :color="$p->is_dispensed ? 'success' : 'warning'">
                                        {{ $p->is_dispensed ? 'Dispensed' : 'Pending' }}
                                    </x-badge>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        @if(!$p->is_dispensed)
                                            <button wire:click="dispense({{ $p->id }})" wire:loading.attr="disabled" wire:target="dispense({{ $p->id }})"
                                                    class="btn btn-primary px-3 py-1.5 text-xs">
                                                <span wire:loading.remove wire:target="dispense({{ $p->id }})">Mark Dispensed</span>
                                                <span wire:loading wire:target="dispense({{ $p->id }})">Saving...</span>
                                            </button>
                                        @else
                                            <p class="text-[10px] text-gray-400 font-medium italic">Dispensed at {{ $p->dispensed_at ? $p->dispensed_at->format('H:i') : 'N/A' }}</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No prescription orders found..." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($prescriptions->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $prescriptions->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
