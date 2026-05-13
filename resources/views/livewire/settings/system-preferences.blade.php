<div>
    <x-breadcrumb :items="['Settings' => route('settings.index'), 'System Preferences' => null]" />

    <x-card title="System Preferences" subtitle="Global configuration for currency, timezones, and numbering formats.">
        <form wire:submit="save" class="space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Currency and Timezone -->
                <x-form.select label="Timezone" wire:model="timezone" name="timezone">
                    <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">America/New_York</option>
                </x-form.select>

                <x-form.input label="Currency Symbol" wire:model="currency_symbol" name="currency_symbol" placeholder="₹" />
                <x-form.input label="Currency Name" wire:model="currency_name" name="currency_name" placeholder="INR" />
                <x-form.select label="Date Format" wire:model="date_format" name="date_format">
                    <option value="d/m/Y">DD/MM/YYYY</option>
                    <option value="Y-m-d">YYYY-MM-DD</option>
                    <option value="m/d/Y">MM/DD/YYYY</option>
                </x-form.select>
            </div>

            <hr class="border-gray-100 dark:border-gray-700/50">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Identifiers -->
                <x-form.input label="UHID Prefix" wire:model="uhid_prefix" name="uhid_prefix" placeholder="HMS-" helpText="Automatically added to all patient IDs" />
                <x-form.input label="Invoice Prefix" wire:model="invoice_prefix" name="invoice_prefix" placeholder="INV-" helpText="Automatically added to all billing records" />
                <x-form.input label="Consultation Fee" wire:model="consultation_fee_default" name="consultation_fee_default" type="number" helpText="Global default for doctors" />
                <x-form.input label="OPD Validity (Days)" wire:model="opd_validity_days" name="opd_validity_days" type="number" helpText="Free follow-up duration" />
            </div>

            <hr class="border-gray-100 dark:border-gray-700/50">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-4 bg-violet-50 dark:bg-violet-900/10 rounded-2xl border border-violet-100 dark:border-violet-800/20 flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center text-violet-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v-3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">Barcode Identification</p>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Enable UHID barcodes on slips and scanning in search</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="enable_barcodes" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600"></div>
                    </label>
                </div>
            </div>


            <div class="flex justify-end pt-6">
                <button type="submit" class="btn-primary w-full sm:w-auto px-12 py-3 text-sm font-bold uppercase tracking-widest shadow-indigo-500/20">
                    Save Changes
                </button>
            </div>
        </form>
    </x-card>
</div>
