<div>
    <x-page-header title="Stock Movements" subtitle="Track all inflows, outflows, and corrections across inventory items.">
        <x-slot name="actions">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Inventory List</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Filters">
                <div class="space-y-4">
                    <div>
                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2 block">Movement Type</label>
                        <div class="space-y-2">
                             @foreach(['All', 'in', 'out', 'correction'] as $t)
                                <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $typeFilter === $t ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900 shadow-sm' : 'border-gray-100 dark:border-gray-800' }}">
                                    <input type="radio" wire:model.live="typeFilter" value="{{ $t }}" class="text-violet-600 focus:ring-violet-500">
                                    <span class="text-xs font-bold uppercase tracking-widest {{ $typeFilter === $t ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">
                                        @if($t === 'All') All History @elseif($t === 'in') Stock Inflow (+) @elseif($t === 'out') Stock Outflow (-) @else Stock Correction @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
                    <x-form.input placeholder="Search items in history..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>

                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Date / Item</x-table.th>
                            <x-table.th>Movement</x-table.th>
                            <x-table.th class="hidden md:table-cell">Notes / User</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td>
                                    <p class="text-tiny font-black text-gray-500 uppercase tracking-widest">{{ $t->created_at->format('d M, Y H:i') }}</p>
                                    <p class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ $t->item?->name ?? 'Deleted Item' }}</p>
                                </td>
                                <td>
                                    @php
                                        $color = match($t->type) { 'in' => 'success', 'out' => 'danger', 'correction' => 'info', default => 'warning' };
                                        $prefix = match($t->type) { 'in' => '+', 'out' => '-', 'correction' => '=', default => '' };
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <x-badge :color="$color">{{ strtoupper($t->type) }}</x-badge>
                                        <span class="text-sm font-black {{ $t->type === 'out' ? 'text-red-600' : ($t->type === 'in' ? 'text-emerald-600' : '') }}">
                                            {{ $prefix }}{{ $t->quantity }} {{ $t->item?->unit }}
                                        </span>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs text-gray-700 dark:text-gray-300 italic">"{{ $t->notes ?: 'No records' }}"</p>
                                    <p class="text-tiny text-gray-400 font-bold uppercase tracking-widest mt-0.5">Recorded By: {{ $t->creator?->name ?? 'System' }}</p>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="3" message="No stock transactions recorded yet." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($transactions->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
