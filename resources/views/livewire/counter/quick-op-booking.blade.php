<div x-data="{}" @open-modal.window="if($event.detail.name === 'quick-op-modal') { setTimeout(() => { (document.getElementById('quick-appointment-weight') || document.getElementById('quick-appointment-search'))?.focus(); }, 200); }">
    <x-modal name="quick-op-modal" :title="$isIpd ? 'Quick Admission' : 'Quick Appointment'" width="3xl" persistent>
        <div class="p-6">
            @if(!$selectedPatient)
                <!-- Search Section -->
                <div class="mb-6">
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-0">Search By</label>
                            <button type="button" 
                                    @click="$dispatch('close-modal', { name: 'quick-op-modal' }); 
                                            $dispatch('create-patient', { 
                                                phone: '{{ ($searchType === 'phone' || ($searchType === 'all' && is_numeric($searchPatient) && strlen($searchPatient) === 10)) ? $searchPatient : '' }}',
                                                name: '{{ ($searchType === 'name' || ($searchType === 'all' && !is_numeric($searchPatient))) ? $searchPatient : '' }}',
                                                mother_name: '{{ $searchType === 'mother_name' ? $searchPatient : '' }}'
                                            })" 
                                    class="btn btn-primary px-6 py-2.5 rounded-xl text-[10px] shadow-lg shadow-indigo-500/20">
                                <svg class="w-3.5 h-3.5 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Register New Patient
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            @foreach([
                                'all' => 'General',
                                'uhid' => 'UHID',
                                'phone' => 'Mobile',
                                'name' => 'Name',
                                'mother_name' => 'Mother'
                            ] as $value => $label)
                                <button 
                                    type="button"
                                    wire:click="$set('searchType', '{{ $value }}')"
                                    @click="setTimeout(() => { document.getElementById('quick-appointment-search')?.focus(); }, 50)"
                                    class="px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border-2 {{ $searchType === $value ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-50 dark:bg-gray-900 border-transparent text-gray-500 hover:border-gray-200 dark:hover:border-gray-700' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative group">
                        <input 
                            type="text"
                            wire:model.live.debounce.300ms="searchPatient"
                            wire:keydown.enter="handleEnter"
                            x-ref="searchInput"
                            id="quick-appointment-search"
                            placeholder="Search by {{ [
                                'all' => 'name or mobile',
                                'uhid' => 'UHID',
                                'phone' => 'mobile number',
                                'name' => 'patient name',
                                'mother_name' => 'mother\'s name'
                            ][$searchType] ?? $searchType }}..."
                            class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-6 py-4 text-gray-900 dark:text-white outline-none transition-all font-bold uppercase tracking-widest text-sm"
                        />
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                            <kbd class="hidden sm:inline-block px-2 py-1 text-[8px] font-black bg-gray-200 dark:bg-gray-800 text-gray-500 rounded-lg">ESC</kbd>
                        </div>
                    </div>
                    
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
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                                ID: {{ $p->uhid }} · {{ $p->phone }}
                                                @if($p->mother_name)
                                                    <span class="text-indigo-500/70 dark:text-indigo-400/70"> · MOTHER: {{ $p->mother_name }}</span>
                                                @endif
                                            </p>
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
                            <button @click="$dispatch('close-modal', { name: 'quick-op-modal' }); 
                                            $dispatch('create-patient', { 
                                                phone: '{{ ($searchType === 'phone' || ($searchType === 'all' && is_numeric($searchPatient) && strlen($searchPatient) === 10)) ? $searchPatient : '' }}',
                                                name: '{{ ($searchType === 'name' || ($searchType === 'all' && !is_numeric($searchPatient))) ? $searchPatient : '' }}',
                                                mother_name: '{{ $searchType === 'mother_name' ? $searchPatient : '' }}'
                                            })" 
                                    class="btn btn-primary px-8 py-3 rounded-xl shadow-lg shadow-indigo-500/20">
                                Register New Patient
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <!-- Booking Section -->
                 <div class="mb-8 relative">
                    <x-clinical.patient-strip :patient="$selectedPatient" size="md" />
                    
                    <!-- Edit Patient Button -->
                    <button type="button" 
                            @click="$dispatch('close-modal', { name: 'quick-op-modal' }); $dispatch('edit-patient', { id: {{ $selectedPatient->id }} })"
                            class="absolute top-2 right-2 p-2.5 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 rounded-xl shadow-xl border border-indigo-100 dark:border-indigo-900/30 hover:scale-110 active:scale-95 transition-all z-20"
                            title="Update Patient Profile">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>

                    @if($isNewbornBenefit)
                        <div class="absolute -top-3 -right-3">
                            <span class="bg-emerald-600 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-lg border-2 border-white dark:border-gray-900 uppercase tracking-widest animate-bounce">
                                Newborn Benefit
                            </span>
                        </div>
                    @elseif($isEmergency)
                        <div class="absolute -top-3 -right-3">
                            <span class="bg-amber-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-lg border-2 border-white dark:border-gray-900 uppercase tracking-widest">
                                Emergency Shift
                            </span>
                        </div>
                    @elseif($isReview)
                        <div class="absolute -top-3 -right-3">
                            <span class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-lg border-2 border-white dark:border-gray-900 uppercase tracking-widest animate-pulse">
                                Review Visit
                            </span>
                        </div>
                    @elseif($isFollowUp)
                        <div class="absolute -top-3 -right-3">
                            <span class="bg-emerald-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-lg border-2 border-white dark:border-gray-900 uppercase tracking-widest">
                                Follow-up Visit
                            </span>
                        </div>
                    @endif
                </div>

                @if($isNewbornBenefit)
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/30 rounded-2xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Newborn Privilege</p>
                            <p class="text-xs font-bold text-emerald-700 dark:text-indigo-300">Free consultation for newborns attended by our doctors (Valid till age 8 days).</p>
                        </div>
                    </div>
                @elseif($isEmergency)
                    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-800/30 rounded-2xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Emergency Shift Pricing</p>
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">After-hours or Sunday shift premium applied (₹500 flat fee).</p>
                        </div>
                    </div>
                @elseif($isReview)
                    <div class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/30 rounded-2xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Review Consultation</p>
                            <p class="text-xs font-bold text-indigo-700 dark:text-indigo-300">This is a follow up review to previous visit (within {{ $latestConsultation->service?->validity_days ?? \App\Models\Setting::get('opd_validity_days', 7) }} days).</p>
                        </div>
                    </div>
                @elseif($isFollowUp)
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/30 rounded-2xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Free Consultation</p>
                            <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">This patient's previous visit is still within the {{ \App\Models\Setting::get('opd_validity_days', 7) }} day validity period.</p>
                        </div>
                    </div>
                @endif
                @if($latestConsultation)
                    <div class="mb-6 grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Previous Visit</p>
                            <p class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-tight">
                                {{ $latestConsultation->consultation_date->format('d M Y') }}
                            </p>
                            <p class="text-[10px] font-bold text-gray-500 mt-0.5 truncate uppercase tracking-widest">{{ $latestConsultation->service?->name ?? 'OPD' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Last Seen By</p>
                            <p class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tight truncate">
                                {{ $latestConsultation->doctor?->full_name ?? 'N/A' }}
                            </p>
                            <p class="text-[10px] font-bold text-gray-500 mt-0.5 uppercase tracking-widest">{{ $latestConsultation->doctor?->specialization ?? 'Department' }}</p>
                        </div>
                    </div>
                @endif
                
                <form wire:submit.prevent="book" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                Appointment Details
                            </h4>
                            
                            @if(!$isIpd)
                                @if(!$isReview)
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
                                @else
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/50 rounded-2xl">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Review Service</p>
                                    <p class="text-sm font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">
                                        {{ $latestConsultation->service?->name ?? 'OPD Consultation' }}
                                    </p>
                                    <p class="text-[10px] font-bold text-indigo-500 mt-0.5 uppercase tracking-widest">AUTO-SELECTED FOR REVIEW</p>
                                </div>
                                @endif

                                <div class="p-4 bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100/50 dark:border-indigo-800/20 rounded-2xl">
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-1">Assigned Doctor</p>
                                    <p class="text-sm font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $this->assignedDoctorName }}</p>
                                </div>
                            @else
                                <!-- IPD Admission Fields -->
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Select Doctor</label>
                                    <select wire:model="selectedDoctor" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white appearance-none transition-all outline-none">
                                        <option value="">Select Doctor...</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedDoctor') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Admission Date & Time</label>
                                    <input type="datetime-local" wire:model="admissionDate" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white transition-all outline-none" />
                                    @error('admissionDate') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Admission Number</label>
                                    <div class="flex items-stretch rounded-2xl bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus-within:border-indigo-500 transition-all">
                                        <span class="flex items-center pl-5 pr-1.5 text-xs font-black text-gray-400 select-none uppercase tracking-wider">
                                            {{ $this->getAdmissionNumberPrefix() }}
                                        </span>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.500ms="manualAdmissionNumber" 
                                            placeholder="ENTER NUMBER..." 
                                            class="flex-1 bg-transparent border-none focus:ring-0 pl-0.5 pr-5 py-4 font-bold text-gray-900 dark:text-white text-sm outline-none uppercase tracking-wide"
                                        />
                                    </div>
                                    @error('manualAdmissionNumber') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-1.5" x-data="{ open: false, search: @entangle('reason') }">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Reason for Admission</label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            wire:model.live="reason" 
                                            @focus="open = true"
                                            @click.away="open = false"
                                            class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all" 
                                            placeholder="TYPE OR SELECT REASON..."
                                        />
                                        <div x-show="open && search.length >= 0" class="absolute z-50 left-0 right-0 mt-2 p-2 bg-white dark:bg-gray-900 rounded-2xl shadow-3xl border border-gray-100 dark:border-gray-800 max-h-48 overflow-y-auto custom-scrollbar">
                                            @foreach($reasons as $r)
                                                <button 
                                                    type="button"
                                                    @click="search = '{{ $r->content }}'; open = false"
                                                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wide transition-colors"
                                                >
                                                    {{ $r->content }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('reason') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">WT (kg)</label>
                                    <input type="number" step="0.1" id="quick-appointment-weight" wire:model.live.debounce.500ms="weight" x-ref="weightInput" placeholder="0.0" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">HT (cm)</label>
                                    <input type="number" step="0.1" wire:model.live.debounce.500ms="height" placeholder="0.0" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Temp (°F)</label>
                                    <input type="number" step="0.1" wire:model="temperature" placeholder="98.6" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                                </div>
                            </div>

                            @if($isIpd)
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Pulse (bpm)</label>
                                        <input type="number" wire:model="pulse" placeholder="72" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">BP (Sys/Dia)</label>
                                        <div class="flex gap-2">
                                            <input type="text" wire:model="bp_systolic" placeholder="120" class="w-1/2 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-2 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm text-center" />
                                            <span class="self-center text-gray-400 font-black">/</span>
                                            <input type="text" wire:model="bp_diastolic" placeholder="80" class="w-1/2 bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-2 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm text-center" />
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Resp. Rate</label>
                                        <input type="number" wire:model="respiratory_rate" placeholder="18" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">SpO2 (%)</label>
                                        <input type="number" wire:model="spo2" placeholder="98" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
                                    </div>
                                </div>
                            @endif

                            @if($growthStatus)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Growth Tracking: {{ $growthStatus['age_label'] }}</h5>
                                    <div class="flex gap-2">
                                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase {{ str_replace('text-', 'bg-', str_replace('600', '100', $growthStatus['weight']['status_color'])) }} {{ $growthStatus['weight']['status_color'] }}">
                                            WT: {{ $growthStatus['weight']['status'] }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase {{ str_replace('text-', 'bg-', str_replace('600', '100', $growthStatus['height']['status_color'])) }} {{ $growthStatus['height']['status_color'] }}">
                                            HT: {{ $growthStatus['height']['status'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="p-2 bg-white/50 dark:bg-gray-800/50 rounded-xl">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">W. Range (kg)</p>
                                        <p class="text-[11px] font-black text-gray-700 dark:text-gray-300">{{ $growthStatus['weight']['expected_range'] }}</p>
                                    </div>
                                    <div class="p-2 bg-white/50 dark:bg-gray-800/50 rounded-xl text-right">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">H. Range (cm)</p>
                                        <p class="text-[11px] font-black text-gray-700 dark:text-gray-300">{{ $growthStatus['height']['expected_range'] }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 h-32" wire:ignore>
                                    <canvas id="growthWeightCanvas"></canvas>
                                    <canvas id="growthHeightCanvas"></canvas>
                                </div>

                                @if($growthForecast)
                                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-800">
                                    <h6 class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">Growth Forecast (Expected Median)</h6>
                                    <div class="flex gap-2">
                                        @foreach($growthForecast as $milestone)
                                        <div class="flex-1 p-2 bg-indigo-50/50 dark:bg-indigo-900/20 rounded-xl text-center">
                                            <p class="text-[8px] font-black text-indigo-500 uppercase">{{ $milestone['label'] }}</p>
                                            <p class="text-[10px] font-black text-gray-700 dark:text-gray-300">{{ number_format($milestone['data']['weight'], 1) }}kg</p>
                                            <p class="text-[8px] font-bold text-gray-500">{{ number_format($milestone['data']['height'], 1) }}cm</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                <script>
                                    document.addEventListener('livewire:initialized', () => {
                                        let weightChart = null;
                                        let heightChart = null;

                                        const initChart = (data) => {
                                            const wCtx = document.getElementById('growthWeightCanvas');
                                            const hCtx = document.getElementById('growthHeightCanvas');
                                            if (!wCtx || !hCtx) return;

                                            if (weightChart) weightChart.destroy();
                                            if (heightChart) heightChart.destroy();

                                            const commonOptions = {
                                                responsive: true, maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: { 
                                                    y: { display: false },
                                                    x: { display: true, grid: { display: false }, ticks: { font: { size: 7 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 3 } }
                                                }
                                            };

                                            weightChart = new Chart(wCtx, {
                                                type: 'line',
                                                data: {
                                                    labels: data.labels,
                                                    datasets: [
                                                        { label: 'Actual', data: data.weight.actual, borderColor: '#4f46e5', backgroundColor: '#4f46e5', pointRadius: 6, pointHoverRadius: 8, showLine: false, zIndex: 10 },
                                                        { label: 'Median (P50)', data: data.weight.median, borderColor: '#10b981', borderWidth: 2, pointRadius: 0, fill: false },
                                                        { label: 'Max (P97)', data: data.weight.max, borderColor: '#fca5a5', borderWidth: 1, pointRadius: 0, borderDash: [5, 5], fill: false },
                                                        { label: 'Min (P3)', data: data.weight.min, borderColor: '#fca5a5', borderWidth: 1, pointRadius: 0, borderDash: [5, 5], fill: false }
                                                    ]
                                                },
                                                options: commonOptions
                                            });

                                            heightChart = new Chart(hCtx, {
                                                type: 'line',
                                                data: {
                                                    labels: data.labels,
                                                    datasets: [
                                                        { label: 'Actual', data: data.height.actual, borderColor: '#059669', backgroundColor: '#059669', pointRadius: 6, pointHoverRadius: 8, showLine: false, zIndex: 10 },
                                                        { label: 'Median (P50)', data: data.height.median, borderColor: '#0ea5e9', borderWidth: 2, pointRadius: 0, fill: false },
                                                        { label: 'Max (P97)', data: data.height.max, borderColor: '#fca5a5', borderWidth: 1, pointRadius: 0, borderDash: [5, 5], fill: false },
                                                        { label: 'Min (P3)', data: data.height.min, borderColor: '#fca5a5', borderWidth: 1, pointRadius: 0, borderDash: [5, 5], fill: false }
                                                    ]
                                                },
                                                options: commonOptions
                                            });
                                        };

                                        if (@json($growthStatus)) initChart(@json($growthStatus['chart_data'] ?? null));

                                        Livewire.on('growth-status-updated', (event) => {
                                            initChart(event[0].chart_data);
                                        });
                                    });
                                </script>
                            </div>
                            @endif
                        </div>

                        @if(!$isIpd)
                            @if(!$isReview && !$isNewbornBenefit && !$isFollowUp)
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
                            @else
                            <div class="space-y-4">
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Payment Status
                                </h4>
                                <div class="bg-emerald-500 p-5 rounded-3xl text-white shadow-2xl shadow-emerald-500/20 relative overflow-hidden group">
                                    <div class="relative z-10">
                                        <p class="text-[10px] font-black uppercase opacity-60 tracking-widest mb-2">
                                            {{ $isNewbornBenefit ? 'Newborn Benefit' : ($isReview ? 'Review Visit' : 'Follow-up Visit') }}
                                        </p>
                                        <p class="text-2xl font-black mb-4">No Charge</p>
                                        <p class="text-[10px] font-bold opacity-80 leading-relaxed uppercase tracking-widest">
                                            {{ $isNewbornBenefit 
                                                ? 'Complimentary consultation for newborns born at our facility.' 
                                                : 'This visit is within the validity period of a previous consultation.' }}
                                        </p>
                                    </div>
                                    <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                            @endif
                        @else
                            <!-- IPD Ward & Bed Selection -->
                            <div class="space-y-4">
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Accommodation Details
                                </h4>
                                <div class="space-y-4 bg-gray-50 dark:bg-gray-900/50 p-5 rounded-3xl border border-gray-100 dark:border-gray-800">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Select Ward</label>
                                        <select wire:model.live="wardId" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 font-bold text-gray-900 dark:text-white appearance-none transition-all outline-none">
                                            <option value="">Select Ward...</option>
                                            @foreach($wards as $ward)
                                                <option value="{{ $ward->id }}">{{ $ward->name }} ({{ $ward->daily_charge ? '₹'.number_format($ward->daily_charge) : 'No Charge' }})</option>
                                            @endforeach
                                        </select>
                                        @error('wardId') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="space-y-3">
                                         <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1 block">Choose a Bed</label>
                                         <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                             @forelse($this->availableBeds as $bed)
                                                 <button 
                                                     type="button"
                                                     wire:click="$set('bedId', {{ $bed->id }})"
                                                     class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all {{ $bedId == $bed->id ? 'bg-indigo-600 border-indigo-600 text-white shadow-xl shadow-indigo-500/30' : 'bg-white dark:bg-gray-950 border-gray-100 dark:border-gray-800 hover:border-indigo-300' }}"
                                                 >
                                                     <svg class="w-6 h-6 mb-1 opacity-60 group-hover:opacity-100 transition-opacity {{ $bedId == $bed->id ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                     <span class="text-[11px] font-black text-center leading-tight {{ $bedId == $bed->id ? 'text-white' : 'text-gray-700 dark:text-gray-300' }}">{{ $bed->bed_number }}</span>
                                                     @if($bedId == $bed->id)
                                                         <div class="absolute -top-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center shadow">
                                                             <svg class="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg>
                                                         </div>
                                                     @endif
                                                 </button>
                                             @empty
                                                 <div class="col-span-full py-6 bg-gray-50 dark:bg-gray-950 rounded-2xl border-2 border-dashed border-gray-100 dark:border-gray-800 text-center">
                                                     <p class="text-xs text-gray-400 font-black uppercase tracking-widest">{{ !$wardId ? 'Select ward first' : 'No beds available' }}</p>
                                                 </div>
                                             @endforelse
                                         </div>
                                         @error('bedId') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1">{{ $message }}</p> @enderror
                                     </div>
                                </div>
                            </div>
                        @endif
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
                            @if(!$activeBookingFound || $isIpd)
                                <button type="submit" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-primary px-10 py-4 shadow-xl shadow-indigo-500/30 rounded-2xl group transition-all active:scale-95">
                                    <span wire:loading.remove>{{ $isIpd ? 'Confirm Admission & Print' : 'Confirm & Print' }}</span>
                                    <span wire:loading>Finalizing...</span>
                                </button>
                            @else
                                <div class="px-8 py-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">Active Booking Found</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </x-modal>
</div>
