<div>
    <x-page-header 
        title="Patient Admission" 
        subtitle="Start a new patient stay, assign a ward, and manage bed availability in real-time."
        back="{{ route('counter.ipd.index') }}"
    >
        <x-slot name="actions">
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary border-indigo-200 dark:border-indigo-900 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                Patient List
            </a>
        </x-slot>
    </x-page-header>

    <!-- Rapid Insights Hub -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10 mt-8">
        <div class="group relative p-6 rounded-[2rem] border border-violet-100 dark:border-violet-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-violet-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-950/40 flex items-center justify-center text-violet-600 group-hover:rotate-6 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-400 opacity-60">Currently Admitted</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['total_active'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Total Active Patients</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-violet-500/5 rounded-full blur-2xl group-hover:bg-violet-500/10 transition-colors"></div>
        </div>

        <div class="group relative p-6 rounded-[2rem] border border-indigo-100 dark:border-indigo-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 opacity-60">Today's Visits</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['total_today'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">New Visits Today</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
        </div>

        <div class="group relative p-6 rounded-[2rem] border border-emerald-100 dark:border-emerald-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 group-hover:-translate-y-1 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400 opacity-60">Free Beds</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['beds_available'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Available Bed Slots</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
        </div>

        <div class="group relative p-6 rounded-[2rem] border border-amber-100 dark:border-amber-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-600 group-hover:rotate-12 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-400 opacity-60">Total Capacity</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['beds_total'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Registered Ward Beds</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
        </div>
    </div>

    <!-- Persistent Command Center: Search & Rapid Discovery -->
    <div class="mb-10 p-1 bg-white dark:bg-gray-900 rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 transition-all focus-within:shadow-indigo-500/10 relative">
        <div class="flex flex-col lg:flex-row items-stretch gap-1">
            <div x-data="{}" x-init="$nextTick(() => $refs.searchInput.focus())" class="flex-1 relative group p-2">
                <div class="absolute left-10 top-1/2 -translate-y-1/2 text-indigo-500 group-focus-within:scale-110 transition-transform">
                    <svg wire:loading.remove wire:target="searchPatient" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <svg wire:loading wire:target="searchPatient" class="w-6 h-6 animate-spin text-indigo-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <input 
                    type="text"
                    placeholder="SEARCH FOR PATIENT: NAME, PATIENT ID OR MOBILE..."
                    wire:model.live.debounce.300ms="searchPatient"
                    x-ref="searchInput"
                    class="w-full bg-gray-50 dark:bg-gray-950 border-none rounded-[2.5rem] pl-20 pr-10 py-7 text-lg font-black tracking-widest text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase"
                />
            </div>

            @if(count($patients) || (strlen($searchPatient) >= 3 && count($patients) === 0))
                <div class="absolute left-0 right-0 top-full mt-4 z-[50] p-4 bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-3xl animate-in slide-in-from-top-4 duration-300">
                    @if(count($patients))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($patients as $p)
                                <div wire:click="selectPatient({{ $p->id }})" class="group cursor-pointer p-5 rounded-2xl bg-gray-50/50 dark:bg-gray-800/30 border border-transparent hover:border-indigo-500 hover:bg-white dark:hover:bg-gray-950 transition-all flex items-center justify-between shadow-sm hover:shadow-xl">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center font-black text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all uppercase">
                                            {{ substr($p->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ $p->full_name }}</p>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ID: {{ $p->uhid }} · {{ $p->phone }}</p>
                                        </div>
                                    </div>
                                    <div class="px-3 py-1 bg-indigo-500 text-white rounded-lg text-[9px] font-black opacity-0 group-hover:opacity-100 transition-opacity">SELECT</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 flex flex-col items-center justify-center text-center">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <h4 class="text-xl font-black text-gray-900 dark:text-white mb-2">Patient Not Found</h4>
                            <p class="text-tiny font-black text-gray-400 uppercase tracking-widest mb-8">Search term: "{{ $searchPatient }}"</p>
                            <button wire:click="$dispatch('open-modal', { name: 'patient-form' })" 
                                    class="px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 transition-all active:scale-95">
                                Register New Patient
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-10">
        @if($patientId)
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <x-clinical.patient-strip :patient="$patient" size="lg" :active="true" />
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Admission Parameters -->
            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-2 h-8 bg-violet-500 rounded-full"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Doctor & Date</h3>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 space-y-8">
                    @if(count($doctors) > 1)
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Select Doctor</label>
                            <select wire:model="doctorId" class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-violet-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white appearance-none text-sm shadow-sm ring-4 ring-gray-100/50 dark:ring-gray-900/50">
                                <option value="">Select Doctor...</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">Dr. {{ $doctor->full_name }} ({{ $doctor->department?->name ?? 'None' }})</option>
                                @endforeach
                            </select>
                            @error('doctorId') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <input type="hidden" wire:model="doctorId">
                    @endif

                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Admission Date & Time</label>
                        <input type="datetime-local" wire:model="admissionDate" class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-violet-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm ring-4 ring-gray-100/50 dark:ring-gray-900/50">
                        @error('admissionDate') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-3" x-data="{ open: false, search: @entangle('reason') }">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Reason for Admission</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live="reason" 
                                @focus="open = true"
                                @click.away="open = false"
                                placeholder="TYPE OR SELECT REASON..." 
                                class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-violet-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm placeholder-gray-300 dark:placeholder-gray-700 ring-4 ring-gray-100/50 dark:ring-gray-900/50 uppercase tracking-wide"
                            >
                            <div x-show="open" class="absolute z-50 left-0 right-0 mt-2 p-2 bg-white dark:bg-gray-900 rounded-2xl shadow-3xl border border-gray-100 dark:border-gray-800 max-h-48 overflow-y-auto custom-scrollbar">
                                @foreach($reasons as $r)
                                    <button 
                                        type="button"
                                        @click="search = '{{ $r->content }}'; open = false"
                                        class="w-full text-left px-4 py-3 hover:bg-violet-50 dark:hover:bg-violet-900/20 rounded-xl text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wide transition-colors"
                                    >
                                        {{ $r->content }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('reason') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-4 mb-2 mt-8">
                    <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Growth & Health Metrics</h3>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 grid grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Weight (kg)</label>
                        <input type="number" step="0.01" wire:model="weight" placeholder="0.00" class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-emerald-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm ring-4 ring-gray-100/50 dark:ring-gray-900/50">
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Height (cm)</label>
                        <input type="number" step="0.1" wire:model="height" placeholder="0.0" class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-emerald-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm ring-4 ring-gray-100/50 dark:ring-gray-900/50">
                    </div>
                </div>

                <div class="flex items-center gap-4 mb-2 mt-8">
                    <div class="w-2 h-8 bg-indigo-500 rounded-full"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Observations</h3>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 space-y-6">
                    <div class="space-y-4" x-data="{ open: false, search: @entangle('notes') }">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Clinical Notes</label>
                        <div class="relative">
                            <textarea 
                                wire:model.live="notes" 
                                @focus="open = true"
                                @click.away="open = false"
                                rows="3" 
                                class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-3xl px-6 py-5 outline-none transition-all font-bold text-gray-900 dark:text-white text-sm shadow-sm placeholder-gray-300 dark:placeholder-gray-700 ring-4 ring-gray-100/50 dark:ring-gray-900/50" 
                                placeholder="ENTER COMPREHENSIVE OBSERVATIONS OR SELECT BELOW..."
                            ></textarea>
                            
                            <div x-show="open" class="absolute z-50 left-0 right-0 mt-2 p-2 bg-white dark:bg-gray-900 rounded-2xl shadow-3xl border border-gray-100 dark:border-gray-800 max-h-48 overflow-y-auto custom-scrollbar">
                                @foreach($clinicalNotes as $n)
                                    <button 
                                        type="button"
                                        @click="search = search ? (search + '\n' + '{{ $n->content }}') : '{{ $n->content }}'; open = false"
                                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wide transition-colors"
                                    >
                                        {{ $n->content }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facility Assignment -->
            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Ward & Bed</h3>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 space-y-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Select Ward</label>
                        <select wire:model.live="wardId" class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-emerald-500 rounded-2xl px-6 py-5 outline-none transition-all font-black text-gray-900 dark:text-white appearance-none text-sm shadow-sm ring-4 ring-gray-100/50 dark:ring-gray-900/50">
                            <option value="">Select Ward...</option>
                            @foreach($wards as $ward)
                                <option value="{{ $ward->id }}">{{ strtoupper($ward->name) }} ({{ strtoupper($ward->type) }})</option>
                            @endforeach
                        </select>
                        @error('wardId') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 block mb-4">Choose a Bed</label>
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                            @forelse($this->availableBeds as $bed)
                                <button 
                                    type="button"
                                    wire:click="$set('bedId', {{ $bed->id }})"
                                    class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all {{ $bedId == $bed->id ? 'bg-emerald-600 border-emerald-600 text-white shadow-xl shadow-emerald-500/30' : 'bg-gray-50 dark:bg-gray-950 border-transparent hover:border-emerald-300' }}"
                                >
                                    <svg class="w-6 h-6 mb-1 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    <span class="text-xs font-black">{{ $bed->bed_number }}</span>
                                    @if($bedId == $bed->id)
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg>
                                        </div>
                                    @endif
                                </button>
                            @empty
                                <div class="col-span-full py-10 bg-gray-50 dark:bg-gray-950 rounded-3xl border-2 border-dashed border-gray-100 dark:border-gray-800 text-center">
                                    <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Select ward to see beds</p>
                                </div>
                            @endforelse
                        </div>
                        @error('bedId') <p class="text-[10px] font-black text-rose-500 mt-2 uppercase tracking-tight">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] ml-2 block">Comprehensive Clinical Observer Notes</label>
            <textarea wire:model="notes" rows="4" class="w-full bg-white dark:bg-gray-900 border-2 border-gray-100 dark:border-gray-800 focus:border-indigo-500 rounded-[2.5rem] px-8 py-6 outline-none transition-all font-bold placeholder-gray-300 dark:placeholder-gray-700 shadow-2xl shadow-indigo-500/5" placeholder="Record comorbidities, specialized nursing protocols, or emergency contact details..."></textarea>
        </div>

        <!-- Submission Command Bar -->
        <div class="pt-10 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h4 class="text-tiny font-black text-gray-900 dark:text-white uppercase tracking-widest">Check Details</h4>
                    <p class="text-[9px] text-gray-400 font-bold uppercase">Please review all information before saving</p>
                </div>
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto">
                <a href="{{ route('counter.ipd.index') }}" class="flex-1 sm:flex-none px-10 py-5 text-sm font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors text-center">Cancel</a>
                <button type="submit" class="flex-1 sm:flex-none px-12 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 transition-all active:scale-95 flex items-center justify-center gap-3">
                    <span wire:loading.remove>Save Admission</span>
                    <span wire:loading class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                </button>
            </div>
        </div>
    </form>

    <livewire:counter.patient-form />

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.2); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.4); }
    </style>
</div>
