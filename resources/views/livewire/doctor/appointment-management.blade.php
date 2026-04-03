<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
            @foreach(['all' => 'All', 'Pending' => 'Pending', 'Upcoming' => 'Upcoming', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'] as $key => $label)
                <button 
                    wire:click="setStatus('{{ $key }}')"
                    class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ $status === $key ? 'bg-violet-600 text-white shadow-lg' : 'bg-white dark:bg-gray-900 text-gray-400 hover:text-violet-600 border border-gray-100 dark:border-gray-800' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <div class="w-full md:w-72">
            <x-form.input wire:model.live.debounce.350ms="search" placeholder="Search Patient or UHID..." id="app-search" />
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Patient</x-table.th>
                    <x-table.th>Date</x-table.th>
                    <x-table.th>Token</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th class="text-right">Actions</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $app)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 font-bold uppercase">
                                    {{ substr($app->patient->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $app->patient->full_name }}</p>
                                    <p class="text-tiny text-gray-400 font-bold tracking-widest uppercase">{{ $app->patient->uhid }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $app->consultation_date->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded text-xs font-mono">
                                #{{ str_pad($app->token_number, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $color = [
                                    'Pending' => 'amber',
                                    'Completed' => 'emerald',
                                    'Cancelled' => 'rose',
                                    'In Progress' => 'indigo'
                                ][$app->status] ?? 'gray';
                            @endphp
                            <x-badge color="{{ $color }}">{{ $app->status }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button class="btn btn-ghost btn-xs text-violet-600 font-bold">Details</button>
                                @if($app->status === 'Completed')
                                    <a href="{{ route('doctor.prescription.print', $app->id) }}" target="_blank" class="btn btn-ghost btn-xs text-emerald-600 font-bold">RX</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-table.empty colspan="5" />
                @endforelse
            </tbody>
        </x-table.wrapper>
        <div class="px-6 py-4 border-t border-gray-50 dark:border-gray-800">
            {{ $appointments->links() }}
        </div>
    </div>
</div>
