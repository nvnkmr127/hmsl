<div>
    <x-breadcrumb :items="['Settings' => route('settings.index'), 'Hospital Details' => null]" />

    <x-card title="Hospital Information" subtitle="Physical address and digital contact details of the medical facility.">
        <form wire:submit="save" class="space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Name and Tagline -->
                <div class="space-y-6">
                    <x-form.input label="Hospital Name" wire:model="hospital_name" name="hospital_name" placeholder="e.g. City Care Hospital" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    
                    <x-form.input label="Tagline" wire:model="hospital_tagline" name="hospital_tagline" placeholder="e.g. Healing Hands, Caring Hearts" />
                </div>

                <!-- Logo Profile -->
                <div class="flex flex-col items-center justify-center p-6 bg-gray-50/50 dark:bg-gray-700/20 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700/50 transition-colors hover:border-indigo-500/50">
                    <div class="w-24 h-24 rounded-2xl bg-white dark:bg-gray-800 shadow-xl flex items-center justify-center overflow-hidden mb-4 border border-gray-100 dark:border-gray-700">
                        @if($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif($currentLogo)
                            <img src="{{ Storage::disk('public')->url($currentLogo) }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @endif
                    </div>
                    <label class="cursor-pointer">
                        <span class="btn-primary flex items-center space-x-2 text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>Upload Logo</span>
                        </span>
                        <input type="file" wire:model="logo" class="hidden">
                    </label>
                    <p class="text-tiny text-gray-400 mt-2 font-bold uppercase tracking-widest">JPG, PNG, GIF Max 2MB</p>
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-700/50">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact Details -->
                <x-form.input label="Phone Number" wire:model="hospital_phone" name="hospital_phone" placeholder="+91 98765 43210" icon="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                <x-form.input label="Email Address" wire:model="hospital_email" name="hospital_email" placeholder="contact@hospital.com" icon="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                <x-form.input label="Website URL" wire:model="hospital_website" name="hospital_website" placeholder="https://www.hospital.com" icon="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <x-form.textarea label="Full Address" wire:model="hospital_address" name="hospital_address" placeholder="Premises number, Street name, Area..." />
                
                <div class="grid grid-cols-2 gap-4">
                    <x-form.input label="City" wire:model="hospital_city" name="hospital_city" placeholder="Bengaluru" />
                    <x-form.input label="State" wire:model="hospital_state" name="hospital_state" placeholder="Karnataka" />
                    <div class="col-span-2">
                        <x-form.input label="Pincode" wire:model="hospital_pincode" name="hospital_pincode" placeholder="560XXX" />
                    </div>
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
