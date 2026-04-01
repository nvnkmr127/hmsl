<div>
    <x-page-header 
        title="OPD Registration & Token Desk" 
        subtitle="Manage daily outpatient visits, generate tokens, and monitor real-time queue status."
    >
        <x-slot name="actions">
            @if($selectedPatient)
                <a href="{{ route('counter.patients.history', ['id' => $selectedPatient->id]) }}" class="btn btn-ghost text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back to {{ explode(' ', $selectedPatient->full_name)[0] }}'s Profile
                </a>
            @endif
            <button wire:click="openPatientForm" class="btn btn-primary">New Patient</button>
            <a href="{{ route('counter.patients.index') }}" class="btn btn-secondary">Patients</a>
            <a href="{{ route('public.queue') }}" target="_blank" class="btn btn-secondary">Public Display</a>
        </x-slot>
    </x-page-header>

    <livewire:counter.patient-form />

    @if(config('app.debug'))
        <div class="fixed bottom-4 left-4 z-[9999] p-4 bg-black/90 text-emerald-400 rounded-3xl border border-emerald-500/30 text-[10px] font-mono shadow-2xl backdrop-blur-xl">
            <p class="mb-2 font-black uppercase text-white/50 border-b border-white/10 pb-1">System Debug Monitor</p>
            <div class="space-y-1">
                <p>Selected Patient: <span class="{{ $selectedPatient ? 'text-emerald-400' : 'text-rose-400' }}">{{ $selectedPatient ? $selectedPatient->id : 'NULL' }}</span></p>
                <p>Editing ID: <span class="{{ $editingId ? 'text-amber-400' : 'text-gray-500' }}">{{ $editingId ?: 'NONE' }}</span></p>
                <p>Search Query: <span class="text-indigo-400">"{{ $searchPatient }}"</span></p>
                <p>Last Token: <span class="text-white">{{ $lastConsultationId ?: 'NA' }}</span></p>
            </div>
        </div>
    @endif

    <!-- Real-time Quick Stats Dashboard -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 mt-6">
        <div class="relative overflow-hidden group p-5 rounded-3xl border border-indigo-100 dark:border-indigo-900/30 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500/60 dark:text-indigo-400/50 mb-1">Total Booked</p>
                    <h4 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['total'] }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
        </div>

        <div class="relative overflow-hidden group p-5 rounded-3xl border border-amber-100 dark:border-amber-900/30 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-500">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/60 dark:text-amber-400/50 mb-1">In Waiting</p>
                    <h4 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['pending'] }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
        </div>

        <div class="relative overflow-hidden group p-5 rounded-3xl border border-emerald-100 dark:border-emerald-900/30 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500/60 dark:text-emerald-400/50 mb-1">Served</p>
                    <h4 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['completed'] }}</h4>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
        </div>

        <div class="relative overflow-hidden group p-5 rounded-3xl border border-violet-100 dark:border-violet-900/30 bg-violet-600 dark:bg-violet-900 hover:shadow-2xl hover:shadow-violet-500/30 transition-all duration-500 cursor-pointer"
             wire:click="openPatientForm" wire:loading.class="opacity-70">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/60 mb-1">Quick Action</p>
                    <h4 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                        New Patient
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    </h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:bg-white group-hover:text-violet-600 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-colors"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Patient Discovery Section -->
        <div class="lg:col-span-4 space-y-6">
            <div class="p-6 rounded-[2.5rem] bg-indigo-950 text-white shadow-2xl relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/40">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-black uppercase tracking-widest text-xs">Patient Lookup</h3>
                                <p class="text-[10px] text-indigo-300 font-bold">Search by Mobile, UHID or Name</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div x-data="{}" x-init="$nextTick(() => $refs.searchInput.focus())" class="relative group">
                            <input 
                                type="text"
                                placeholder="Start typing mobile number..."
                                wire:model.live.debounce.300ms="searchPatient"
                                x-ref="searchInput"
                                id="patient-search-input"
                                class="w-full bg-indigo-900/50 border-2 border-indigo-500/20 focus:border-indigo-500 rounded-2xl px-6 py-4 text-white placeholder-indigo-400 outline-none transition-all duration-300 pr-12 text-sm font-bold uppercase tracking-wider"
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-indigo-400 group-hover:text-indigo-200 transition-colors">
                                <svg wire:loading.remove wire:target="searchPatient" class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2-2v14a2 2 0 002 2z" /></svg>
                                <svg wire:loading wire:target="searchPatient" class="w-5 h-5 animate-spin text-indigo-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>

                        @if(count($patients))
                            <div class="mt-4 space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($patients as $p)
                                    <div class="group/item flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/5 hover:border-indigo-500/30 transition-all duration-300 cursor-pointer shadow-sm"
                                         wire:click="selectPatient({{ $p->id }})">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center border border-indigo-500/20 group-hover/item:bg-indigo-500 group-hover/item:scale-110 transition-all duration-500">
                                                <span class="text-lg font-black text-indigo-400 group-hover/item:text-white">{{ strtoupper(substr($p->first_name, 0, 1)) }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-black text-sm uppercase tracking-tight text-white group-hover/item:text-indigo-300 transition-colors">{{ $p->full_name }}</span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-indigo-300/60 group-hover/item:text-white/60 font-bold tracking-widest">{{ $p->uhid }}</span>
                                                    <span class="w-0.5 h-0.5 rounded-full bg-indigo-500/30"></span>
                                                    <span class="text-[10px] text-emerald-400 group-hover/item:text-emerald-300 font-black tracking-widest">{{ $p->phone }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 rounded-xl bg-white/5 group-hover/item:bg-indigo-500 flex items-center justify-center transition-all duration-300">
                                            <svg class="w-4 h-4 text-white/20 group-hover/item:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strlen($searchPatient) >= 3)

                            <div class="mt-6 p-8 text-center bg-indigo-900/40 rounded-[2rem] border-2 border-dashed border-indigo-500/20">
                                <div class="w-16 h-16 bg-indigo-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <h4 class="text-sm font-black uppercase tracking-widest text-white mb-2">New Arrival?</h4>
                                <p class="text-[10px] text-indigo-300 font-bold mb-6 italic">No match found for <span class="text-white">"{{ $searchPatient }}"</span></p>
                                <button wire:click="openPatientForm('{{ $searchPatient }}')" 
                                        class="w-full py-4 bg-white text-indigo-950 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl hover:scale-[1.02] active:scale-95 transition-all">
                                    Quick Register
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Background Decorative SVG -->
                <svg class="absolute -right-10 -top-10 w-48 h-48 text-indigo-500/10 rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 12l11 10 11-10L12 2zm0 18.5L2.5 12 12 3.5l9.5 8.5L12 20.5z"/></svg>
            </div>

            <a href="{{ route('public.queue') }}" target="_blank" class="p-6 rounded-[2.5rem] bg-emerald-600 dark:bg-emerald-900 shadow-2xl relative overflow-hidden group cursor-pointer block">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <h3 class="text-white font-black uppercase tracking-widest text-xs mb-1">Public Display</h3>
                        <p class="text-[10px] text-emerald-100 font-bold">Real-time TV Queue Monitor</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-white group-hover:bg-white group-hover:text-emerald-600 transition-all">
                        <svg class="w-5 h-5 font-black uppercase tracking-widest" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Consultation Monitoring Section -->
        <div class="lg:col-span-8">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                    <div>
                        <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">Daily Consultations</h2>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-black uppercase tracking-widest mt-0.5">Live view for {{ date('D, d M Y') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                         <!-- Multi-color Dot Indicator -->
                         <div class="flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-900 dark:text-white">Active Desk</span>
                         </div>
                    </div>
                </div>

                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($todayConsultations as $consult)
                        <div class="p-5 {{ $consult->status === 'Cancelled' ? 'opacity-40 grayscale' : '' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm">#{{ str_pad($consult->token_number, 2, '0', STR_PAD_LEFT) }}</span>
                                        @php 
                                            $statusThemes = [
                                                'Pending' => ['bg' => 'bg-amber-100 text-amber-600', 'label' => 'Waiting'],
                                                'Ongoing' => ['bg' => 'bg-indigo-100 text-indigo-600', 'label' => 'Ongoing'],
                                                'Completed' => ['bg' => 'bg-emerald-100 text-emerald-600', 'label' => 'Done'],
                                                'Cancelled' => ['bg' => 'bg-rose-100 text-rose-600', 'label' => 'Cancelled'],
                                            ];
                                            $theme = $statusThemes[$consult->status] ?? ['bg' => 'bg-gray-100 text-gray-600', 'label' => $consult->status];
                                        @endphp
                                        <span class="{{ $theme['bg'] }} px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-[0.1em]">
                                            {{ $theme['label'] }}
                                        </span>
                                    </div>
                                    <p class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm truncate mt-2">
                                        {{ $consult->patient->full_name }}
                                    </p>
                                    <p class="text-[10px] font-black tracking-widest text-violet-600 dark:text-violet-400 uppercase truncate mt-0.5">
                                        Dr. {{ $consult->doctor->full_name }}
                                    </p>
                                    <p class="text-[10px] font-black tracking-widest text-gray-400 uppercase truncate mt-0.5">
                                        {{ $consult->patient->uhid }} · {{ $consult->patient->gender }} · {{ $consult->patient->age }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    @if($consult->payment_status === 'Paid')
                                        <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-lg text-[10px] font-black uppercase">PAID</span>
                                        <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase">{{ $consult->payment_method }}</p>
                                    @else
                                        <span class="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-3 py-1 rounded-lg text-[10px] font-black uppercase">UNPAID</span>
                                    @endif
                                </div>
                            </div>

                            @if($consult->status !== 'Cancelled')
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button @click="$dispatch('record-vitals', { patientId: {{ $consult->patient_id }}, consultationId: {{ $consult->id }} })"
                                            class="btn btn-secondary px-3 py-2 text-xs">
                                        Vitals
                                    </button>
                                    <a href="{{ route('counter.opd.print', ['id' => $consult->id]) }}" target="_blank"
                                       class="btn btn-secondary px-3 py-2 text-xs">
                                        Print
                                    </a>
                                    @if($consult->payment_status === 'Paid')
                                    <button wire:click="$dispatch('generate-bill', {{ $consult->id }})"
                                            class="btn btn-secondary px-3 py-2 text-xs">
                                        Bill
                                    </button>
                                    @endif
                                    <button wire:click="editBooking({{ $consult->id }})"
                                            class="btn btn-secondary px-3 py-2 text-xs">
                                        Edit
                                    </button>
                                    <button wire:click="cancelBooking({{ $consult->id }})"
                                            wire:confirm="Permanent cancellation of #{{ $consult->token_number }}?"
                                            class="btn btn-ghost px-3 py-2 text-xs">
                                        Cancel
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <p class="text-sm font-black uppercase tracking-widest text-gray-400">The queue is currently empty</p>
                        </div>
                    @endforelse
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white dark:bg-gray-900">
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800">Token</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800">Patient</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800 text-center">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800 text-center">Billing</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($todayConsultations as $consult)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-all duration-300 {{ $consult->status === 'Cancelled' ? 'opacity-40 grayscale' : '' }}">
                                    <td class="px-8 py-5">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 flex flex-col items-center justify-center border border-indigo-100/50 dark:border-indigo-900/30">
                                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-tighter leading-none mb-0.5">TKN</span>
                                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 leading-none">#{{ str_pad($consult->token_number, 2, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm">{{ $consult->patient->full_name }}</span>
                                                    @if($consult->patient->vitals->count() > 0)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" title="Vitals recorded"></span>
                                                    @else
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" title="Vitals missing"></span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                    <span>{{ $consult->patient->uhid }}</span>
                                                    <span class="w-1 h-1 rounded-full bg-gray-200 dark:bg-gray-700"></span>
                                                    <span>{{ $consult->patient->gender }} · {{ $consult->patient->age }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        @php 
                                            $statusThemes = [
                                                'Pending' => ['bg' => 'bg-amber-100 text-amber-600', 'label' => 'Waiting'],
                                                'Ongoing' => ['bg' => 'bg-indigo-100 text-indigo-600', 'label' => 'Ongoing'],
                                                'Completed' => ['bg' => 'bg-emerald-100 text-emerald-600', 'label' => 'Done'],
                                                'Cancelled' => ['bg' => 'bg-rose-100 text-rose-600', 'label' => 'Cancelled'],
                                            ];
                                            $theme = $statusThemes[$consult->status] ?? ['bg' => 'bg-gray-100 text-gray-600', 'label' => $consult->status];
                                        @endphp
                                        <span class="{{ $theme['bg'] }} px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-[0.1em] ring-4 ring-white dark:ring-gray-950">
                                            {{ $theme['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        @if($consult->payment_status === 'Paid')
                                            <div class="flex flex-col items-center">
                                                <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-lg text-[10px] font-black uppercase">PAID</span>
                                                <span class="text-[9px] font-bold text-gray-400 mt-1 uppercase">{{ $consult->payment_method }}</span>
                                            </div>
                                        @else
                                            <span class="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-3 py-1 rounded-lg text-[10px] font-black uppercase">UNPAID</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($consult->status !== 'Cancelled')
                                                <button @click="$dispatch('record-vitals', { patientId: {{ $consult->patient_id }}, consultationId: {{ $consult->id }} })" 
                                                        class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-emerald-500 hover:text-white transition-all duration-300 shadow-sm" title="Take Vitals">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                                </button>

                                                <a href="{{ route('counter.opd.print', ['id' => $consult->id]) }}" target="_blank"
                                                   class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-indigo-500 hover:text-white transition-all duration-300 shadow-sm" title="Print Slip">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4" /></svg>
                                                </a>

                                                @if($consult->payment_status === 'Paid')
                                                <button wire:click="$dispatch('generate-bill', {{ $consult->id }})"
                                                        class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-amber-500 hover:text-white transition-all duration-300 shadow-sm" title="Generate Bill">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </button>
                                                @endif

                                                <button wire:click="editBooking({{ $consult->id }})" 
                                                        class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-violet-500 hover:text-white transition-all duration-300 shadow-sm" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                </button>

                                                @if($consult->status === 'Cancelled')
                                                    <button wire:click="restoreBooking({{ $consult->id }})" title="Restore Ticket" class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    </button>
                                                @else
                                                    <button wire:click="cancelBooking({{ $consult->id }})" 
                                                            wire:confirm="Permanent cancellation of #{{ $consult->token_number }}?"
                                                            class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-rose-500 hover:text-white transition-all duration-300 shadow-sm" title="Cancel">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center justify-center grayscale opacity-30">
                                            <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                            <p class="text-sm font-black uppercase tracking-widest text-gray-400">The queue is currently empty</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($todayConsultations->hasPages())
                    <div class="px-8 py-6 border-t border-gray-50 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20">
                        {{ $todayConsultations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    <x-modal name="booking-modal" :title="$isEditing ? 'System: Reschedule Visit' : 'Counter: Token Generation'" width="3xl">
        @if($selectedPatient)
            <div class="space-y-6 p-1">
                <!-- Patient Identity Banner -->
                <div class="flex items-center gap-6 p-6 rounded-[2.5rem] bg-indigo-50/50 dark:bg-indigo-950/20 border-2 border-indigo-100/50 dark:border-indigo-900/30 shadow-inner group transition-all">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-xl shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-500">
                        {{ strtoupper(substr($selectedPatient->first_name, 0, 1)) }}
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3">
                            <span class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">{{ $selectedPatient->full_name }}</span>
                            <x-badge color="indigo">{{ $selectedPatient->uhid }}</x-badge>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">{{ $selectedPatient->age }} · {{ $selectedPatient->gender }} · {{ $selectedPatient->phone }}</span>
                        </div>
                    </div>
                </div>

                <form class="space-y-6" wire:submit.prevent="book" x-data="{}" x-init="$nextTick(() => $refs.weightInput.focus())">

                    <!-- Core Booking Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                Assignment Details
                            </h4>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Consulting Doctor</label>
                                <select wire:model.live="selectedDoctor" class="w-full bg-gray-50 dark:bg-gray-900/50 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-bold text-gray-900 dark:text-white appearance-none">
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}">Dr. {{ $doc->full_name }} ({{ $doc->department?->name }})</option>
                                    @endforeach
                                </select>
                                @error('selectedDoctor') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Appointment Date</label>
                                <input type="date" wire:model.live="consultation_date" class="w-full bg-gray-50 dark:bg-gray-900/50 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-bold text-gray-900 dark:text-white">
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest ml-1">Validity until {{ \Carbon\Carbon::parse($valid_upto)->format('D, d M' ) }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Basic Vitals
                            </h4>
                            <div class="grid grid-cols-2 gap-4 text-xs font-bold text-gray-600">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Wt (kg)</label>
                                    <input type="number" step="0.1" wire:model="weight" x-ref="weightInput" class="w-full bg-gray-50 dark:bg-gray-900/50 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-black text-gray-900 dark:text-white" placeholder="0.0">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">Temp (°F)</label>
                                    <x-form.input type="number" step="0.1" wire:model="temperature" placeholder="98.6" />
                                </div>
                            </div>

                            <div class="p-5 mt-2 rounded-[2rem] bg-indigo-600 text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden group">
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <label class="text-[10px] font-black text-indigo-200 uppercase tracking-widest">Consultation Fee</label>
                                        <div class="px-2 py-1 bg-white/20 rounded-lg text-[9px] font-black uppercase tracking-tighter">Automatic</div>
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <span class="text-lg font-black opacity-60 pb-1">₹</span>
                                        <input type="number" wire:model="fee" class="w-32 bg-transparent border-none p-0 text-3xl font-black focus:ring-0 outline-none text-white transition-all">
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-t border-white/10">
                                        <select wire:model="paymentMode" class="w-full bg-white/10 hover:bg-white/20 border-none rounded-xl px-4 py-2 font-black text-[10px] uppercase tracking-widest text-white outline-none transition-all">
                                            <option class="text-gray-900" value="Cash">Settlement: Cash</option>
                                            <option class="text-gray-900" value="UPI">Settlement: UPI</option>
                                            <option class="text-gray-900" value="Card">Settlement: Card</option>
                                        </select>
                                    </div>
                                </div>
                                <svg class="absolute -right-8 -bottom-8 w-32 h-32 text-white/10 rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Presentation -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2 flex items-center gap-2">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                             Symptom Manifestation / Notes
                        </label>
                        <textarea wire:model="notes" rows="3" class="w-full bg-gray-50 dark:bg-gray-900/50 border-2 border-transparent focus:border-indigo-500 rounded-[2rem] px-6 py-5 outline-none transition-all font-bold placeholder-gray-300 dark:placeholder-gray-700" placeholder="State main complaints (Fever, Headache, etc...)"></textarea>
                    </div>

                    <!-- Action Interface -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-8 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="$dispatch('close-modal', { name: 'booking-modal' })" 
                                class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Discard Entry
                        </button>
                        
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" 
                                    wire:click="book(false)" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 sm:flex-auto px-8 py-4 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.2em] transition-all disabled:opacity-50">
                                <span wire:loading.remove wire:target="book(false)">Register Only</span>
                                <span wire:loading wire:target="book(false)">Processing...</span>
                            </button>
                            <button type="button" 
                                    wire:click="book(true)" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 sm:flex-auto px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/30 transition-all disabled:opacity-50">
                                <span wire:loading.remove wire:target="book(true)">Generate Token & Print</span>
                                <span wire:loading wire:target="book(true)">Finalizing...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </x-modal>


    <livewire:counter.vital-signs />
    <livewire:counter.bill-generate />

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.2); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.4); }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            console.log('Desk: Livewire System Initialized');

            Livewire.on('print-op-slip', (event) => {
                console.log('Desk: Printing Slip ID ->', event[0].id);
                const url = "{{ route('counter.opd.print', ['id' => ':id']) }}".replace(':id', event[0].id);
                window.open(url, '_blank');
            });

            Livewire.on('bill-generated', (event) => {
                const billId = event?.billId ?? event?.[0]?.billId ?? event?.[0]?.bill_id ?? event?.[0];
                if (!billId) return;
                const url = "{{ route('billing.bills.print', ['bill' => ':id']) }}".replace(':id', billId);
                window.open(url, '_blank');
            });

            Livewire.on('booking-completed', () => {
                console.log('Desk: Booking Success Reset');
                const searchInput = document.getElementById('patient-search-input');
                if (searchInput) {
                    setTimeout(() => {
                        searchInput.focus();
                        searchInput.select();
                    }, 200);
                }
            });

            // Global Modal Open Interceptor for Debugging
            window.addEventListener('open-modal', (event) => {
                const data = event.detail;
                const name = data.name || (Array.isArray(data) ? data[0].name : (typeof data === 'string' ? data : 'UNKNOWN'));
                console.log('Desk: Open Modal Full Detail ->', data);
                console.log('Desk: Resolved Modal Name ->', name);
            });
        });
    </script>
</div>
