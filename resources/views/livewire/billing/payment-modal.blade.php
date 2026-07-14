<div>
    <x-modal name="standalone-billing-payment-modal" title="Collect Payment" width="xl">
        <div class="p-6 space-y-4">
            <x-form.select label="Type" wire:model="paymentType">
                <option value="payment">Payment</option>
                <option value="refund">Refund</option>
            </x-form.select>
            <x-form.select label="Method" wire:model="paymentMethod">
                <option value="Cash">Cash</option>
                <option value="Card">Card / POS</option>
                <option value="UPI">UPI</option>
                <option value="Insurance">Insurance</option>
            </x-form.select>
            <x-form.input type="number" step="1" label="Amount" wire:model.live.debounce.300ms="paymentAmount" class="text-right" />
            <x-form.input type="text" label="Reference (Optional)" wire:model="paymentReference" />
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest pl-1">Notes (Optional)</label>
                <textarea rows="2" class="block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 focus:bg-white dark:focus:bg-gray-800 transition-all duration-300 px-4 py-3 sm:text-sm resize-none" wire:model="paymentNotes"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="$dispatch('close-modal', { name: 'standalone-billing-payment-modal' })" class="btn btn-ghost px-6">Cancel</button>
                <button type="button" wire:click="submitPayment" wire:loading.attr="disabled" wire:target="submitPayment" class="btn btn-primary px-10">Save</button>
            </div>
        </div>
    </x-modal>
</div>
