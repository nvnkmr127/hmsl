<div>
    <x-modal name="quick-op-modal" title="Quick Appointment" width="3xl">
        <div class="p-6">
            @if(!$selectedPatient)
                <!-- Search Section -->
                <div class="mb-6">
                    <label class="block text-sm font-black uppercase tracking-widest text-gray-400 mb-2">Search Patient</label>
                    <input 
                        type="text"
                        wire:model.live.debounce.300ms="searchPatient"
                        placeholder="Search by name or mobile..."
                        class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-6 py-4 text-gray-900 dark:text-white outline-none transition-all font-bold uppercase tracking-widest text-sm"
                    />
                    
                    @if(count($patients))
                        <div class="mt-4 space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                            @foreach($patients as $p)
                                <div wire:click="selectPatient({{ $p->id }})" class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 hover:bg-violet/5 dark:hover:bg-violet/10 cursor-pointer transition-all flex items-center justify-between group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-black text-gray-400 group-hover:bg-violet group-hover:text-white transition-all text-sm">
                                            {{ strtoupper(substr($p->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $p->full_name }}</p>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">ID: {{ $p->uhid }} · {{ $p->phone }}</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-violet transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                            @endforeach
                        </div>
                    @elseif(strlen($searchPatient) >= 3)
                        <div class="mt-4 p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 grayscale opacity-50">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="1.5"/></svg>
                            </div>
                            <p class="text-sm font-black uppercase tracking-widest text-gray-400 mb-4 italic">No matching patient found in our registry.</p>
                            <button @click="$dispatch('create-patient', { phone: '{{ $searchPatient }}' })" 
                                    class="btn btn-primary px-8 py-3 rounded-xl shadow-lg shadow-indigo-500/20">
                                Register New Patient
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <!-- Booking Section -->
                <div class="mb-8">
                   <x-clinical.patient-strip :patient="$selectedPatient" size="md" />
                </div>
                
                <form wire:submit.prevent="book" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                Appointment Details
                            </h4>
                            
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Consultation Service</label>
                                <select wire:model.live="selectedService" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white appearance-none transition-all outline-none">
                                    <option value="">Select Service</option>
                                    @forelse($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} (₹{{ number_format($service->price ?? 0, 0) }})</option>
                                    @empty
                                        <option value="">No services available</option>
                                    @endforelse
                                </select>
                                @error('selectedService') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Select Doctor</label>
                                <select wire:model.live="selectedDoctor" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white appearance-none transition-all outline-none">
                                    <option value="">Select Doctor</option>
                                    @forelse($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">Dr. {{ $doctor->user?->name ?? 'Unknown' }} @if($doctor->department) · {{ $doctor->department->name }} @endif @if($doctor->consultation_fee) (₹{{ number_format($doctor->consultation_fee, 0) }}) @endif</option>
                                    @empty
                                        <option value="">No doctors available</option>
                                    @endforelse
                                </select>
                                @error('selectedDoctor') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Payment Details
                            </h4>
                            <div class="bg-violet p-5 rounded-3xl text-white shadow-2xl shadow-violet-500/20 relative overflow-hidden group">
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-[10px] font-black uppercase opacity-60 tracking-widest">Amount</span>
                                        <div class="px-2 py-0.5 bg-white/20 rounded-lg text-dense font-black">CALCULATED</div>
                                    </div>
                                    <div class="flex items-end gap-1 mb-6">
                                        <span class="text-2xl font-black mb-0.5">₹</span>
                                        <input type="number" wire:model="fee" class="bg-transparent border-none p-0 text-4xl font-black focus:ring-0 text-white w-full outline-none" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest opacity-60 ml-1">Payment Method</label>
                                        <select wire:model="paymentMode" class="w-full bg-white/10 border-none text-white focus:ring-1 focus:ring-white/30 rounded-xl px-4 py-3 font-bold transition-all outline-none">
                                            <option class="text-gray-900" value="Cash">Settlement: Cash</option>
                                            <option class="text-gray-900" value="UPI">Settlement: UPI</option>
                                            <option class="text-gray-900" value="Card">Settlement: Card</option>
                                        </select>
                                        @error('fee') <p class="text-[10px] font-bold text-white/80 mt-1">{{ $message }}</p> @enderror
                                        @error('paymentMode') <p class="text-[10px] font-bold text-white/80 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <svg class="absolute -right-8 -bottom-8 w-32 h-32 text-white/5 opacity-10 rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 12l11 10 11-10L12 2zm0 18.5L2.5 12 12 3.5l9.5 8.5L12 20.5z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                        <button type="button" 
                                wire:click="$set('selectedPatient', null)" 
                                class="px-6 py-3 text-tiny font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">
                            Change Patient
                        </button>
                        
                        <div class="flex items-center gap-3">
                            <button type="button" @click="$dispatch('close-modal', { name: 'quick-op-modal' })" 
                                    class="px-6 py-3 text-tiny font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 transition-all">
                                Cancel
                            </button>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="btn btn-primary px-10 py-4 shadow-xl shadow-indigo-500/30 rounded-2xl group transition-all active:scale-95">
                                <span wire:loading.remove>Confirm & Print</span>
                                <span wire:loading>Finalizing...</span>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </x-modal>
</div>
