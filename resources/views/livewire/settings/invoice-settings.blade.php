<div>
    <x-breadcrumb :items="['Settings' => route('settings.index'), 'Invoice & Print Settings' => null]" />

    <x-card title="Invoice & Print Preferences" subtitle="Configuration for medical bills, prescription layouts, and tax calculations.">
        <form wire:submit="save" class="space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Tax Configuration -->
                <div class="space-y-6">
                    <x-form.checkbox label="Show Tax on Invoices" wire:model="show_tax" name="show_tax" />
                    <x-form.input label="Tax Percentage (%)" wire:model="tax_percentage" name="tax_percentage" type="number" placeholder="18" />
                </div>

                <!-- Paper Size -->
                <x-form.select label="Print Paper Size" wire:model="print_paper_size" name="print_paper_size">
                    <option value="A4">A4 Standard</option>
                    <option value="A5">A5 Small</option>
                    <option value="LETTER">Letter</option>
                </x-form.select>
            </div>

            <hr class="border-gray-100 dark:border-gray-700/50">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Textual Content -->
                <x-form.textarea label="Default Invoice Header" wire:model="invoice_header" name="invoice_header" placeholder="Enter header text appearing on all bills..." helpText="Accepts plain text, no HTML." />
                <x-form.textarea label="Default Invoice Footer" wire:model="invoice_footer" name="invoice_footer" placeholder="Enter footer text, disclaimer, etc." />
            </div>

            <div class="flex justify-end pt-6">
                <button type="submit" class="btn-primary w-full sm:w-auto px-12 py-3 text-sm font-bold uppercase tracking-widest shadow-indigo-500/20">
                    Save Changes
                </button>
            </div>
        </form>
    </x-card>
</div>
