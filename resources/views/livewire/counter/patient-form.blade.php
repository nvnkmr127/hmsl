<div>
    <x-modal name="patient-modal" :title="$isEditing ? 'Update Patient Information' : 'New Patient Registration'" width="3xl">
        <form wire:submit="save" class="space-y-8 p-1">
            <!-- Identity Section -->
            <div class="space-y-6">
                <!-- Child Full Name (Prominent) -->
                <div x-data="{}" x-init="$nextTick(() => $refs.firstName.focus())" class="grid grid-cols-1 gap-4">
                    <div class="p-6 rounded-[2rem] bg-indigo-50/50 dark:bg-indigo-950/20 border-2 border-indigo-100/50 dark:border-indigo-900/30 group transition-all focus-within:border-indigo-500">
                        <label class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] ml-2 mb-3 block">Patient (Child) Full Name</label>
                        <input type="text" wire:model="first_name" x-ref="firstName" placeholder="Enter Child's Full Name" 
                               class="w-full bg-transparent border-none p-0 text-2xl font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 outline-none transition-all shadow-none ring-0 focus:ring-0">
                        @error('first_name') <p class="text-[10px] text-rose-500 font-black uppercase mt-2 ml-2">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Parents Info Group -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-form.input label="Mother Name" wire:model="mother_name" name="mother_name" placeholder="Mother's Full Name" />
                        @error('mother_name') <p class="text-[10px] text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                    <x-form.input label="Father Name (Optional)" wire:model="father_name" name="father_name" placeholder="Father's Full Name" />
                </div>

                <!-- Physical Details & Contact -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div>
                        <x-form.input label="Age (Years)" wire:model="age" name="age" type="number" placeholder="Enter Age" />
                        @error('age') <p class="text-[10px] text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-form.select label="Gender" wire:model="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </x-form.select>
                        @error('gender') <p class="text-[10px] text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-form.input label="Mobile Phone" wire:model="phone" name="phone" placeholder="10 Digit Number" />
                        @error('phone') <p class="text-[10px] text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-8 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'patient-modal' })" 
                        class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Discard
                </button>
                <button type="submit" class="px-12 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/30 hover:scale-[1.02] active:scale-95 transition-all">
                    {{ $isEditing ? 'Update Profile' : 'Register & Proceed' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
