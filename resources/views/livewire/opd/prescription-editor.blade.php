<div>
    <div class="space-y-3">
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
            <h4 class="font-bold text-gray-900 dark:text-white mb-3">Add Medicine</h4>

            <div class="mb-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Medicine Name</label>
                <div class="relative">
                    <input type="text" wire:model.live="searchMedicine" wire:focus="$set('showSearch', true)" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Search medicine...">
                    @if($showSearch && $medicines && $medicines->count() > 0)
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xl max-h-48 overflow-y-auto">
                            @foreach($medicines as $med)
                                <button wire:click="selectMedicine({{ $med->id }}, '{{ $med->name }}')" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                                    <span class="font-semibold">{{ $med->name }}</span>
                                    @if($med->strength)
                                        <span class="text-gray-500 ml-1">{{ $med->strength }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if($selectedMedicineName)
                    <p class="text-xs text-indigo-600 mt-1">Selected: {{ $selectedMedicineName }}</p>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Dosage</label>
                    <input type="text" wire:model.live="dosage" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., 500mg">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Frequency</label>
                    <select wire:model.live="frequency" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                        <option value="">Select</option>
                        @foreach($frequencyOptions as $freq)
                            <option value="{{ $freq }}">{{ $freq }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Duration</label>
                    <input type="text" wire:model.live="duration" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., 5 days">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Route</label>
                    <select wire:model.live="route" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                        @foreach($routeOptions as $route)
                            <option value="{{ $route }}">{{ $route }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Instructions</label>
                <input type="text" wire:model.live="instructions" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., After food">
            </div>

            <div class="flex justify-end">
                <button wire:click="addMedicine" class="btn btn-primary text-xs">Add Medicine</button>
            </div>
        </div>

        @if(count($medicines))
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-gray-900 dark:text-white">Prescribed Medicines ({{ count($medicines) }})</h4>
                    <button wire:click="savePrescription" class="btn btn-primary text-xs">Save Prescription</button>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Medicine</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Dosage</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Frequency</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Duration</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Route</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Instructions</th>
                            <th class="text-right py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicines as $index => $med)
                            <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                <td class="py-2 font-semibold">{{ $med['medicine_name'] }}</td>
                                <td class="py-2">{{ $med['dosage'] ?? '-' }}</td>
                                <td class="py-2">{{ $med['frequency'] ?? '-' }}</td>
                                <td class="py-2">{{ $med['duration'] ?? '-' }}</td>
                                <td class="py-2">{{ $med['route'] ?? '-' }}</td>
                                <td class="py-2 text-gray-500">{{ $med['instructions'] ?? '-' }}</td>
                                <td class="py-2 text-right">
                                    <button wire:click="removeMedicine({{ $index }})" class="text-rose-500 hover:text-rose-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <p class="text-sm">No medicines prescribed yet.</p>
            </div>
        @endif
    </div>
</div>
