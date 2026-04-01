<div>
    <x-modal name="medicine-modal" :title="$isEditing ? 'Edit Medicine Details' : 'Add New Inventory Item'" width="3xl">
        <form wire:submit="save" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Branding and Classification -->
                <div class="space-y-5">
                    <p class="section-lbl" style="color:#7c3aed">Classification</p>
                    <x-form.input label="Medicine Code (HNS/SKU)" wire:model="code" name="code" placeholder="e.g. MED-001" />
                    <x-form.input label="Brand Name" wire:model="name" name="name" placeholder="e.g. Paracetamol" />
                    <x-form.input label="Generic Formula" wire:model="generic_name" name="generic_name" placeholder="e.g. Acetaminophen" />

                    
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.select label="Dosage Form" wire:model="category" name="category">
                            <option value="">Select Type</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Syrup">Syrup</option>
                            <option value="Injection">Injection</option>
                            <option value="Ointment">Ointment</option>
                            <option value="Drops">Drops</option>
                            <option value="Others">Others</option>
                        </x-form.select>
                        <x-form.input label="Strength" wire:model="strength" name="strength" placeholder="e.g. 500mg" />
                    </div>
                    <x-form.input label="Manufacturer" wire:model="manufacturer" name="manufacturer" placeholder="e.g. Sun Pharma" />
                </div>

                <!-- Commercials and Stock -->
                <div class="space-y-5">
                    <p class="section-lbl" style="color:#7c3aed">Inventory & Pricing</p>
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input label="Cost Price (₹)" wire:model="buying_price" name="buying_price" type="number" step="0.01" />
                        <x-form.input label="M.R.P (₹)" wire:model="selling_price" name="selling_price" type="number" step="0.01" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input label="Current Qty" wire:model="stock_quantity" name="stock_quantity" type="number" />
                        <x-form.input label="Alert Qty" wire:model="min_stock_level" name="min_stock_level" type="number" />
                    </div>

                    <x-form.input label="Expiry Date" wire:model="expire_date" name="expire_date" type="date" />
                    <div class="pt-2">
                        <x-form.checkbox label="Enable for Prescription" wire:model="is_active" name="is_active" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'medicine-modal' })" 
                        class="btn btn-ghost">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Inventory' : 'Add to Stock' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
