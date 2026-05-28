<div>
    @if(!$hideTrigger)
    <button x-data @click="$dispatch('open-modal', { name: 'discharge-process-modal' })"
        class="btn btn-primary w-full text-xs">
        Start Discharge Process
    </button>
    @endif

    <x-modal name="discharge-process-modal" title="Discharge Process & Final Billing" maxWidth="5xl">
        <div class="p-0 bg-gray-50/50 dark:bg-gray-900/50">
            <div class="p-6 space-y-8 max-h-[75vh] overflow-y-auto">
                
                <!-- Bed/Ward Charges -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
                    <div class="flex justify-between items-center mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Ward & Bed Charges
                        </h3>
                    </div>
                    <div class="space-y-4">
                        @foreach($bedCharges as $index => $charge)
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-12 md:col-span-4">
                                    <label class="text-[10px] uppercase font-bold text-gray-500">Ward / Room Type</label>
                                    <select wire:model.live="bedCharges.{{ $index }}.ward_id" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-1">
                                        <option value="">Select Ward</option>
                                        @foreach($wards as $ward)
                                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-span-6 md:col-span-3">
                                    <label class="text-[10px] uppercase font-bold text-gray-500">Start Date</label>
                                    <input type="datetime-local" wire:model.live="bedCharges.{{ $index }}.start_date" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-1">
                                </div>
                                
                                <div class="col-span-6 md:col-span-3">
                                    <label class="text-[10px] uppercase font-bold text-gray-500">End Date</label>
                                    <input type="datetime-local" wire:model.live="bedCharges.{{ $index }}.end_date" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-1">
                                </div>

                                <div class="col-span-12 md:col-span-2 flex justify-between items-end pb-1 md:justify-end">
                                    <button wire:click="removeBedCharge({{ $index }})" class="text-red-500 hover:text-red-700 md:mt-6">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-3 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 items-center">
                                <div>
                                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Days</label>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $charge['days'] ?? 0 }} Days</span>
                                </div>
                                <div>
                                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Price / Day (₹)</label>
                                    <input type="number" wire:model.live="bedCharges.{{ $index }}.price" min="0" step="0.01" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 py-1 bg-gray-50 dark:bg-gray-800" readonly>
                                </div>
                                <div class="text-right">
                                    <label class="text-[10px] uppercase font-bold text-gray-500 block">Total (₹)</label>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">₹{{ number_format($charge['total'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>    
                <button wire:click="addBedCharge" wire:loading.attr="disabled" class="mt-4 px-4 py-2 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50">
                    <svg wire:loading.remove wire:target="addBedCharge" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <svg wire:loading wire:target="addBedCharge" class="animate-spin w-4 h-4 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Add Ward/Bed Charge
                </button>
            </div>

            <!-- IP Services Charges -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="flex justify-between items-center mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        IP Services & Procedures
                    </h3>
                </div>
                <div class="space-y-4">
                    @foreach($ipServiceCharges as $index => $charge)
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-12 md:col-span-4">
                                    <label class="text-[10px] uppercase font-bold text-gray-500">Service Name</label>
                                    <select wire:model.live="ipServiceCharges.{{ $index }}.service_id" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-1">
                                        <option value="">Select Service</option>
                                        <option value="manual">Manual Entry</option>
                                        @foreach($ipServicesList as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }} (₹{{ $service->price }})</option>
                                        @endforeach
                                    </select>
                                    @if(($charge['service_id'] ?? '') === 'manual')
                                        <input type="text" wire:model.live="ipServiceCharges.{{ $index }}.name" placeholder="Enter service description" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-2">
                                    @endif
                                    @error("ipServiceCharges.{$index}.service_id") <span class="text-[10px] text-red-500 block mt-1">{{ $message }}</span> @enderror
                                    @error("ipServiceCharges.{$index}.name") <span class="text-[10px] text-red-500 block mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="col-span-4 md:col-span-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-500">Qty</label>
                                    <input type="number" wire:model.live.debounce.500ms="ipServiceCharges.{{ $index }}.quantity" min="1" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-1">
                                </div>
                                
                                <div class="col-span-4 md:col-span-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-500">Price (₹)</label>
                                    <input type="number" wire:model.live.debounce.500ms="ipServiceCharges.{{ $index }}.price" min="0" step="0.01" class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 mt-1 {{ ($charge['service_id'] ?? '') === 'manual' ? '' : 'bg-gray-50 dark:bg-gray-800' }}" {{ ($charge['service_id'] ?? '') === 'manual' ? '' : 'readonly' }}>
                                </div>
                                
                                <div class="col-span-4 md:col-span-3 text-right md:pt-6 pr-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-500 block md:hidden">Total (₹)</label>
                                    <span class="text-base lg:text-lg font-bold text-gray-900 dark:text-white">₹{{ number_format($charge['total'] ?? 0, 2) }}</span>
                                </div>

                                <div class="col-span-12 md:col-span-1 flex justify-end md:pt-6">
                                    <button wire:click="removeIpServiceCharge({{ $index }})" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>    
                <button wire:click="addIpServiceCharge" wire:loading.attr="disabled" class="mt-4 px-4 py-2 text-xs font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 dark:text-purple-400 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50">
                    <svg wire:loading.remove wire:target="addIpServiceCharge" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <svg wire:loading wire:target="addIpServiceCharge" class="animate-spin w-4 h-4 text-purple-600 dark:text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Add IP Service
                </button>
            </div>

            <!-- Existing In-Patient Charges -->
            @if(count($existingCharges) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="flex justify-between items-center mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        Other In-Patient Charges (Pharmacy, Lab, Consultations)
                    </h3>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center"></th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Price</th>
                                <th class="px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($existingCharges as $charge)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" wire:model.live="selectedExistingCharges" value="{{ $charge['id'] }}" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $charge['name'] }}</td>
                                <td class="px-4 py-3 text-gray-500"><x-badge type="secondary">{{ $charge['type'] }}</x-badge></td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $charge['quantity'] }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">₹{{ number_format($charge['unit_price'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">₹{{ number_format($charge['quantity'] * $charge['unit_price'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            </div>

            <!-- Sticky Footer for Summary & Actions -->
            <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 rounded-b-3xl">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 px-6 py-3 rounded-xl flex items-center justify-between gap-8 border border-blue-100 dark:border-blue-800/30">
                        <h4 class="text-xs font-black text-blue-800 dark:text-blue-300 uppercase tracking-widest">Estimated Final Bill</h4>
                        <div class="flex flex-col items-end">
                            <div class="text-3xl font-black text-blue-900 dark:text-white drop-shadow-sm leading-none">
                                ₹{{ number_format($totalAmount, 2) }}
                            </div>
                            <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold tracking-wide mt-1">Review selected charges before generating</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" @click="$dispatch('close-modal', { name: 'discharge-process-modal' })" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button wire:click="generateBill" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg shadow-indigo-500/30 transition-all flex items-center gap-2" wire:loading.attr="disabled">
                            <svg wire:loading wire:target="generateBill" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span wire:loading.remove wire:target="generateBill">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            Generate Bill & Continue
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>
</div>
