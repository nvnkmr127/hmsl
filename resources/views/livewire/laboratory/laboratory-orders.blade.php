<div>
    <x-page-header title="Laboratory Management" subtitle="View test requests, enter results, and manage lab order statuses.">
        <x-slot name="actions">
            <a href="{{ route('laboratory.tests') }}" class="btn btn-secondary">Tests</a>
            <a href="{{ route('laboratory.results') }}" class="btn btn-secondary">Results</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Order Status">
                <div class="space-y-4">
                    <div class="space-y-2">
                        @foreach(['Pending', 'In Progress', 'Completed', 'Cancelled', 'All'] as $s)
                            <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $status === $s ? 'bg-violet-50 border-violet-200 dark:bg-violet-950/20 dark:border-violet-900 shadow-sm' : 'border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50' }}">
                                <input type="radio" wire:model.live="status" value="{{ $s }}" class="text-violet-600 focus:ring-violet-500">
                                <span class="text-xs font-bold uppercase tracking-widest {{ $status === $s ? 'text-violet-700 dark:text-violet-400' : 'text-gray-500' }}">{{ $s }} Orders</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
                    <x-form.input placeholder="Search orders by patient name, UHID, or test..." wire:model.live.debounce.300ms="search" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </div>

                <div wire:loading.flex wire:target="search,status,selectForResults,submitResults" class="items-center justify-center p-6 text-xs font-black uppercase tracking-widest text-gray-400">
                    Loading lab orders...
                </div>

                <x-table.wrapper wire:loading.remove wire:target="search,status,selectForResults,submitResults">
                    <thead>
                        <tr>
                            <x-table.th>Patient</x-table.th>
                            <x-table.th class="hidden md:table-cell">Test Detail</x-table.th>
                            <x-table.th class="hidden lg:table-cell">Ordered By</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $o)
                            <tr>
                                <td>
                                    <x-patient-identity :patient="$o->patient" />
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $o->labTest?->name ?? 'Unknown Test' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $o->created_at->format('d M, Y H:i') }}</p>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $o->doctor?->full_name ?? 'Unassigned' }}</p>
                                </td>
                                <td>
                                    @php
                                        $color = match($o->status) {
                                            'Pending' => 'warning',
                                            'In Progress' => 'info',
                                            'Completed' => 'success',
                                            default => 'danger'
                                        };
                                    @endphp
                                    <x-badge :color="$color">{{ $o->status }}</x-badge>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        @if($o->status !== 'Completed')
                                            <button wire:click="selectForResults({{ $o->id }})" wire:loading.attr="disabled" wire:target="selectForResults({{ $o->id }})"
                                                    class="btn btn-primary px-3 py-1.5 text-xs">
                                                <span wire:loading.remove wire:target="selectForResults({{ $o->id }})">Enter Results</span>
                                                <span wire:loading wire:target="selectForResults({{ $o->id }})">Opening...</span>
                                            </button>
                                        @else
                                            <button class="btn btn-ghost px-3 py-1.5 text-xs text-violet-600">
                                                View Report
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No laboratory orders found for the selected status..." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>

                @if($orders->hasPages())
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
                        {{ $orders->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Results Modal -->
    <x-modal name="results-modal" title="Enter Test Results">
        @if($selectedOrder)
            <form wire:submit.prevent="submitResults">
                <div class="space-y-6">
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.673.337a4 4 0 01-2.574.344l-1.474-.411a5 5 0 00-3.578.176l-1.41.632"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $selectedOrder->labTest?->name ?? 'Unknown Test' }}</p>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $selectedOrder->patient?->full_name ?? 'Unknown Patient' }} ({{ $selectedOrder->patient?->uhid ?? 'N/A' }})</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($selectedOrder->labTest?->parameters ?? [] as $p)
                            <div class="grid grid-cols-3 items-center gap-4">
                                <div class="col-span-1">
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $p->name }}</p>
                                    <p class="text-[10px] text-gray-400">Range: {{ $p->reference_range }} {{ $p->unit }}</p>
                                </div>
                                <div class="col-span-2">
                                    <x-form.input wire:model="results.{{ $p->id }}" placeholder="Value ({{ $p->unit }})" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="$dispatch('close-modal', { name: 'results-modal' })" class="btn btn-ghost">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="submitResults" class="btn btn-primary">
                        <span wire:loading.remove wire:target="submitResults">Save Results</span>
                        <span wire:loading wire:target="submitResults">Saving...</span>
                    </button>
                </div>
            </form>
        @endif
    </x-modal>
</div>
