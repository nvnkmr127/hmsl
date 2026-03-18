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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Identifiers -->
                <x-form.input label="UHID Prefix" wire:model="uhid_prefix" name="uhid_prefix" placeholder="HMS-" helpText="Automatically added to all patient IDs" />
                <x-form.input label="Invoice Prefix" wire:model="invoice_prefix" name="invoice_prefix" placeholder="INV-" helpText="Automatically added to all billing records" />
                <x-form.input label="Consultation Fee" wire:model="consultation_fee_default" name="consultation_fee_default" type="number" icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </div>

            <div class="flex justify-end pt-6">
                <button type="submit" class="btn-primary w-full sm:w-auto px-12 py-3 text-sm font-bold uppercase tracking-widest shadow-indigo-500/20">
                    Save Changes
                </button>
            </div>
        </form>
    </x-card>
</div>
