<div>
    <x-modal name="billing-modal" :title="'Generate Invoice: ' . $patientName" width="4xl">
        <form wire:submit="save" class="space-y-6">
            <div class="bg-gray-50/50 dark:bg-gray-950/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                <p class="section-lbl mb-4" style="color:#7c3aed">Billing Items</p>
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Description</x-table.th>
                            <x-table.th class="text-center">Qty</x-table.th>
                            <x-table.th class="text-right">Rate</x-table.th>
                            <x-table.th class="text-right">Amount</x-table.th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            <tr>
                                <td>
                                    <x-form.input wire:model="items.{{ $index }}.name" placeholder="Service / Item Name" />
                                </td>
                                <td class="w-24">
                                    <x-form.input type="number" wire:model.live="items.{{ $index }}.quantity" class="text-center" />
                                </td>
                                <td class="w-32">
                                    <x-form.input type="number" step="1" wire:model.live="items.{{ $index }}.unit_price" class="text-right" />
                                </td>
                                <td class="text-right font-bold text-gray-900 dark:text-white px-4">
                                    ₹{{ number_format($item['quantity'] * $item['unit_price'], 2) }}
                                </td>
                                <td>
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table.wrapper>
                <div class="mt-4">
                    <button type="button" wire:click="addItem" class="btn btn-ghost text-violet-600 text-xs font-bold uppercase py-2 h-auto">
                        + Add Billing Line
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <p class="section-lbl" style="color:#7c3aed">Payment Mode</p>
                    <x-form.select label="Status" wire:model="paymentStatus">
                        <option value="Paid">Paid</option>
                        <option value="Partially Paid">Partially Paid</option>
                        <option value="Unpaid">Unpaid</option>
                    </x-form.select>
                    <x-form.select label="Method" wire:model="paymentMethod">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card / POS</option>
                        <option value="UPI">UPI (GPay/PhonePe)</option>
                        <option value="Insurance">Insurance / Corporate</option>
                    </x-form.select>
                    <x-form.input
                        type="number"
                        step="1"
                        label="Amount Paid"
                        wire:model.live="amountPaid"
                        class="text-right"
                    />
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest pl-1">Additional Remittance Notes</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Optional comments for hospital records..." class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 focus:bg-white dark:focus:bg-gray-800 transition-all duration-300 px-4 py-3 sm:text-sm resize-none" wire:model="notes"></textarea>
                    </div>
                </div>
                
                <div class="p-6 rounded-2xl border border-violet-100 dark:border-violet-900/30 space-y-4"
                     style="background:rgba(124,58,237,0.03)">
                    <div class="flex justify-between items-center">
                        <span class="text-tiny font-black text-gray-400 uppercase tracking-widest">Gross Subtotal</span>
                        <span class="font-bold text-gray-900 dark:text-white">₹{{ number_format($this->subtotal, 2) }}</span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-tiny font-black text-gray-400 uppercase tracking-widest">Adjustment (-)</span>
                            <div class="flex items-center gap-2">
                                @if($isAuthorizedByDoctor)
                                    <x-badge color="violet" class="text-[9px]">DR. AUTH: ₹{{ number_format($authorizedLimit, 0) }}</x-badge>
                                @endif
                                <select wire:model.live="discountType" class="text-xs border-gray-200 dark:border-gray-800 rounded-lg bg-white dark:bg-gray-900">
                                    <option value="flat">₹</option>
                                    <option value="percentage">%</option>
                                </select>
                                <div class="w-24">
                                    <x-form.input type="number" step="0.01" wire:model.live="discount" class="text-right" />
                                </div>
                            </div>
                        </div>
                        @if($discount > 0)
                            <div class="bg-white/50 dark:bg-gray-900/50 p-3 rounded-xl border border-violet-100 dark:border-violet-900/30">
                                <x-form.input label="Discount Reason (Mandatory)" wire:model="discountReason" placeholder="e.g. Professional Courtesy, Sole Doctor Disc..." />
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-tiny font-black text-gray-400 uppercase tracking-widest">Applicable Tax (+)</span>
                        <div class="w-32">
                            <x-form.input type="number" step="1" wire:model.live="tax" class="text-right" />
                        </div>
                    </div>
                    <div class="pt-5 mt-2 border-t border-violet-100 dark:border-violet-800 flex justify-between items-end">
                        <div>
                            <p class="text-tiny font-black text-violet-500 uppercase tracking-[0.2em] mb-1">Total Net Payable</p>
                            <span class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">
                                ₹{{ number_format($this->total, 2) }}
                            </span>
                        </div>
                        <div class="pb-1">
                            <x-badge color="success">Verified</x-badge>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'billing-modal' })" 
                        class="btn btn-ghost px-6">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    Generate Invoice
                </button>
            </div>
        </form>
    </x-modal>
</div>
