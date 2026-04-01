<div>
    <x-modal name="doctor-modal" :title="$isEditing ? 'Edit Doctor Record' : 'Add New Medical Staff'" width="4xl">
        <form wire:submit="save" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Personal Info -->
                <div class="space-y-5">
                    <p class="section-lbl" style="color:#7c3aed">Professional Profile</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form.input label="Doctor Code" wire:model="doctor_code" name="doctor_code" placeholder="DOC-001" />
                        <x-form.input label="Full Name" wire:model="full_name" name="full_name" placeholder="Dr. John Doe" />
                    </div>

                    
                    <x-form.select label="Medical Department" wire:model="department_id" name="department_id">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </x-form.select>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form.input label="Specialization" wire:model="specialization" name="specialization" placeholder="e.g. Cardiologist" />
                        <x-form.input label="Qualification" wire:model="qualification" name="qualification" placeholder="e.g. MBBS, MD" />
                    </div>
                </div>

                <!-- Contact & Fees -->
                <div class="space-y-5">
                    <p class="section-lbl" style="color:#7c3aed">Contact & Billing</p>
                    <x-form.input label="Contact Phone" wire:model="phone" name="phone" placeholder="+91 XXXX XXXX" />
                    <x-form.input label="Email Address" wire:model="email" name="email" type="email" placeholder="doctor@hospital.com" />
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form.input label="Consultation Fee (₹)" wire:model="consultation_fee" name="consultation_fee" type="number" />
                        <x-form.input label="License/Reg. Number" wire:model="registration_number" name="registration_number" placeholder="MCI-XXXXXX" />
                    </div>
                </div>
            </div>

            <x-form.textarea label="Biography / Professional Background" wire:model="biography" name="biography" placeholder="Brief details about experience, achievements, etc." rows="3" />

            <div class="flex items-center space-x-2 py-2">
                <x-form.checkbox label="Active and Available for OPD" wire:model="is_active" name="is_active" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'doctor-modal' })" 
                        class="btn btn-ghost">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-10">
                    {{ $isEditing ? 'Update Records' : 'Save Doctor' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
