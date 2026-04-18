<div>
    <x-page-header 
        title="Outpatient Desk" 
        subtitle="Manage daily visits, give tokens, and monitor the waiting list in real-time."
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

    <!-- Quick Stats Hub -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10 mt-8">
        <!-- Total Booked -->
        <div class="group relative p-6 rounded-[2rem] border border-indigo-100 dark:border-indigo-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 group-hover:rotate-6 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 opacity-60">Total Visits</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['total'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Total Little Patients</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
        </div>

        <!-- In Queue -->
        <div class="group relative p-6 rounded-[2rem] border border-amber-100 dark:border-amber-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-400 opacity-60">Waiting</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['pending'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Children Waiting</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-colors"></div>
        </div>

        <!-- Completed -->
        <div class="group relative p-6 rounded-[2rem] border border-emerald-100 dark:border-emerald-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 group-hover:-translate-y-1 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400 opacity-60">Completed</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $stats['completed'] }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Visits Completed</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
        </div>

        <!-- Revenue -->
        <div class="group relative p-6 rounded-[2rem] border border-violet-100 dark:border-violet-900/40 bg-white dark:bg-gray-900 shadow-sm hover:shadow-2xl hover:shadow-violet-500/10 transition-all duration-500 overflow-hidden">
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-950/40 flex items-center justify-center text-violet-600 group-hover:rotate-12 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-400 opacity-60">Today's Income</span>
                </div>
                <div>
                    <h4 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter">₹{{ number_format($stats['revenue'], 0) }}</h4>
                    <p class="text-tiny font-black uppercase tracking-widest text-gray-400 mt-1">Total Payment</p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-violet-500/5 rounded-full blur-2xl group-hover:bg-violet-500/10 transition-colors"></div>
        </div>
    </div>

    <!-- Persistent Command Center: Search & Rapid Discovery -->
    <div class="mb-10 flex flex-col sm:flex-row gap-4 items-stretch">
        <div class="flex-1 p-1 bg-white dark:bg-gray-900 rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 transition-all focus-within:shadow-indigo-500/10 relative">
            <div class="flex flex-col lg:flex-row items-stretch gap-1">
                <!-- Search Focus -->
                <div x-data="{}" x-init="$nextTick(() => $refs.searchInput.focus())" class="flex-1 relative group p-2">
                    <div class="absolute left-10 top-1/2 -translate-y-1/2 text-indigo-500 group-focus-within:scale-110 transition-transform">
                        <svg wire:loading.remove wire:target="searchPatient" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <svg wire:loading wire:target="searchPatient" class="w-6 h-6 animate-spin text-indigo-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <input 
                        type="text"
                        placeholder="SEARCH CHILDREN: NAME, ID OR MOBILE..."
                        wire:model.live.debounce.300ms="searchPatient"
                        x-ref="searchInput"
                        id="patient-search-input"
                        class="w-full bg-gray-50 dark:bg-gray-950 border-none rounded-[2.5rem] pl-20 pr-10 py-7 text-lg font-black tracking-widest text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase"
                    />
                </div>

                <!-- Instant Results Dropdown (Absolute when searching) -->
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
                                        <div class="px-3 py-1 bg-indigo-500 text-white rounded-lg text-[9px] font-black opacity-0 group-hover:opacity-100 transition-opacity">CHOOSE</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <h4 class="text-xl font-black text-gray-900 dark:text-white mb-2">Patient unrecognized in Registry</h4>
                                <p class="text-tiny font-black text-gray-400 uppercase tracking-widest mb-8">System ID: "{{ $searchPatient }}"</p>
                                <button wire:click="openPatientForm('{{ $searchPatient }}')" 
                                        class="px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/40 transition-all active:scale-95">
                                    Register New Child
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Access -->
        <a href="{{ route('public.queue') }}" target="_blank" class="w-full sm:w-64 p-1 bg-emerald-600 dark:bg-emerald-900 rounded-[3rem] shadow-2xl shadow-emerald-500/10 group overflow-hidden relative">
            <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-white mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <h3 class="text-white font-black uppercase tracking-widest text-[10px] mb-0.5">Queue Monitor</h3>
                <p class="text-[9px] text-emerald-100 font-bold opacity-60 uppercase">Launch Public Display</p>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:bg-white/10 transition-colors"></div>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 items-start">

    <div class="lg:col-span-12">
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 overflow-hidden min-h-[460px] flex flex-col">
                <div class="px-8 py-8 border-b border-gray-50 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/30 dark:bg-gray-900/40">
                    <div class="flex items-center gap-4">
                        <div class="w-3 h-12 bg-indigo-500 rounded-full hidden sm:block"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-900 dark:text-white font-black text-[10px] leading-none uppercase tracking-[0.3em] mb-1.5">{{ config('app.name','Children Clinic') }}</p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-gray-400">Specialized Pediatric Care</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                         <div class="px-4 py-2 rounded-2xl bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 shadow-sm flex items-center gap-3 group cursor-help">
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-900 bg-emerald-500"></div>
                                <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-900 bg-amber-500"></div>
                                <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-900 bg-indigo-500"></div>
                            </div>
                            <span class="text-tiny font-black uppercase tracking-widest text-gray-900 dark:text-white">Live Status</span>
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
                                        <x-clinical.status-badge :status="$consult->status" size="sm" />
                                    </div>
                                    <p class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-sm truncate mt-2">
                                        {{ $consult->patient->full_name }}
                                    </p>
                                    <p class="text-tiny font-black tracking-widest text-violet-600 dark:text-violet-400 uppercase truncate mt-0.5">
                                        {{ $consult->service?->name ?? 'OPD Visit' }} · Dr. {{ $consult->doctor?->full_name ?? 'Any' }}
                                    </p>

                                    <p class="text-tiny font-black tracking-widest text-gray-400 uppercase truncate mt-0.5">
                                        {{ $consult->patient->uhid }} · {{ $consult->patient->gender }} · {{ $consult->patient->age }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    @php
                                        $billStatus = $consult->bill?->payment_status;
                                        $paid = $consult->bill ? (float) $consult->bill->paid_amount : 0;
                                        $due = $consult->bill ? max(0, (float) $consult->bill->balance_amount) : 0;
                                    @endphp
                                    @if($billStatus === 'Paid' || $consult->payment_status === 'Paid')
                                        <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-lg text-tiny font-black uppercase">PAID</span>
                                        <p class="text-dense font-bold text-gray-400 mt-1 uppercase">{{ $consult->bill?->payment_method ?? $consult->payment_method }}</p>
                                    @elseif($billStatus === 'Partially Paid')
                                        <span class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-3 py-1 rounded-lg text-tiny font-black uppercase">PARTIAL</span>
                                        <p class="text-[10px] font-bold text-gray-400 mt-1">Paid ₹{{ number_format($paid, 0) }} · Due ₹{{ number_format($due, 0) }}</p>
                                    @else
                                        <span class="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-3 py-1 rounded-lg text-tiny font-black uppercase">UNPAID</span>
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
                                    @if($consult->bill)
                                        <a href="{{ route('billing.bills.print', ['bill' => $consult->bill->id]) }}" 
                                           target="_blank"
                                           class="btn btn-secondary px-3 py-2 text-xs border-emerald-500/30 text-emerald-600 dark:text-emerald-400">
                                            Print Bill
                                        </a>
                                        <a href="{{ route('billing.index', ['search' => $consult->bill->bill_number]) }}" class="btn btn-secondary px-3 py-2 text-xs">
                                            Collect
                                        </a>
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
                            <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-100 dark:border-gray-800">Token #</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-100 dark:border-gray-800">Patient Details</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-100 dark:border-gray-800">Service & Doctor</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-100 dark:border-gray-800 text-center">Payment</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-100 dark:border-gray-800 text-center">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-100 dark:border-gray-800 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($todayConsultations as $consult)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-all duration-300 {{ $consult->status === 'Cancelled' ? 'opacity-40 grayscale' : '' }}">
                                    <td class="px-8 py-5">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 flex flex-col items-center justify-center border border-indigo-100/50 dark:border-indigo-900/30">
                                            <span class="text-tiny font-black text-indigo-400 uppercase tracking-tighter leading-none mb-0.5">TKN</span>
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
                                                <div class="flex items-center gap-2 text-tiny font-black tracking-widest text-gray-400 uppercase">
                                                    <span>ID: {{ $consult->patient->uhid }}</span>
                                                    <span class="w-1 h-1 rounded-full bg-gray-200 dark:bg-gray-700"></span>
                                                    <span>{{ $consult->patient->gender }} · {{ $consult->patient->age }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs">{{ $consult->service?->name ?? 'Consultation' }}</span>
                                            <span class="text-tiny font-black tracking-widest text-violet-600 dark:text-violet-400 uppercase">Dr. {{ $consult->doctor?->full_name ?? 'TBD' }}</span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-8 py-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-black text-gray-900 dark:text-white mb-1">₹{{ number_format($consult->fee, 0) }}</span>
                                            @php
                                                $billStatus = $consult->bill?->payment_status;
                                                $paid = $consult->bill ? (float) $consult->bill->paid_amount : 0;
                                                $due = $consult->bill ? max(0, (float) $consult->bill->balance_amount) : 0;
                                            @endphp
                                            @if($billStatus === 'Paid' || $consult->payment_status === 'Paid')
                                                <div class="flex items-center gap-1.5 px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-md">
                                                    <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                                    <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase italic">{{ $consult->bill?->payment_method ?? $consult->payment_method }}</span>
                                                </div>
                                            @elseif($billStatus === 'Partially Paid')
                                                <div class="flex items-center gap-1.5 px-2 py-0.5 bg-amber-50 dark:bg-amber-950/30 rounded-md">
                                                    <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                                                    <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase italic">Paid ₹{{ number_format($paid, 0) }} · Due ₹{{ number_format($due, 0) }}</span>
                                                </div>
                                            @else
                                                <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest animate-pulse">NOT PAID</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <x-clinical.status-badge :status="$consult->status" size="sm" />
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

                                                @if($consult->bill)
                                                    <a href="{{ route('billing.bills.print', ['bill' => $consult->bill->id]) }}" target="_blank"
                                                       class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-amber-500 hover:text-white transition-all duration-300 shadow-sm" title="Print Bill">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                    </a>
                                                    <a href="{{ route('billing.index', ['search' => $consult->bill->bill_number]) }}"
                                                       class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-emerald-600 hover:text-white transition-all duration-300 shadow-sm" title="Collect">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    </a>
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
    <x-modal name="booking-modal" :title="$isEditing ? 'Reschedule Visit' : 'Create Token'" width="3xl">
        @if($selectedPatient)
            <div class="space-y-6 p-1">
                <!-- Patient Identity Banner -->
                <x-clinical.patient-strip :patient="$selectedPatient" size="lg" :active="true" />

                <form class="space-y-6" wire:submit.prevent="book" x-data="{}" x-init="$nextTick(() => $refs.weightInput.focus())">

                    <!-- Core Booking Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="p-6 rounded-3xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 space-y-6">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                Visit Details
                            </h4>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Consultation Category</label>
                                <select wire:model.live="selectedService" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-black text-gray-900 dark:text-white appearance-none text-sm shadow-sm">
                                    <option value="">Select Service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} (₹{{ number_format($service->price, 0) }})</option>
                                    @endforeach
                                </select>
                                @error('selectedService') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Consulting Physician</label>
                                <select wire:model.live="selectedDoctor" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-black text-gray-900 dark:text-white appearance-none text-sm shadow-sm">
                                    <option value="">Any Available Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">Dr. {{ $doctor->full_name }} ({{ $doctor->department?->name ?? 'General' }})</option>
                                    @endforeach
                                </select>
                                @error('selectedDoctor') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Visit Date</label>
                                <input type="date" wire:model.live="consultation_date" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-5 py-4 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm">
                                @error('consultation_date') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                                <p class="text-[9px] text-indigo-400 font-black uppercase tracking-[0.15em] ml-1 mt-2">Validity Auto-Extended: {{ \Carbon\Carbon::parse($valid_upto)->format('D, d M' ) }}</p>
                            </div>
                        </div>

                        <div class="p-6 rounded-3xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 space-y-6">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Health Check
                                         <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">WT (kg)</label>
                                    <input type="number" step="0.1" wire:model.live.debounce.500ms="weight" x-ref="weightInput" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm" placeholder="0.0">
                                    @error('weight') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">HT (cm)</label>
                                    <input type="number" step="0.1" wire:model.live.debounce.500ms="height" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm" placeholder="0.0">
                                    @error('height') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Temp (°F)</label>
                                    <input type="number" step="0.1" wire:model="temperature" class="w-full bg-white dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 outline-none transition-all font-black text-gray-900 dark:text-white text-sm shadow-sm" placeholder="98.6">
                                    @error('temperature') <span class="text-[10px] font-bold text-rose-500 ml-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
               </div>

                            @if($growthStatus)
                            <div class="p-4 bg-white dark:bg-gray-950 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
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
                                    <div class="p-2 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">W. Range (kg)</p>
                                        <p class="text-[11px] font-black text-gray-700 dark:text-gray-300">{{ $growthStatus['weight']['expected_range'] }}</p>
                                    </div>
                                    <div class="p-2 bg-gray-50 dark:bg-gray-900/50 rounded-xl text-right">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">H. Range (cm)</p>
                                        <p class="text-[11px] font-black text-gray-700 dark:text-gray-300">{{ $growthStatus['height']['expected_range'] }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 h-32" wire:ignore>
                                    <canvas id="growthWeightCanvasFull"></canvas>
                                    <canvas id="growthHeightCanvasFull"></canvas>
                                </div>

                                @if($growthForecast)
                                <div class="mt-4 pt-4 border-t border-dashed border-gray-100 dark:border-gray-800">
                                    <h6 class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">Growth Forecast (Expected Median)</h6>
                                    <div class="flex gap-2">
                                        @foreach($growthForecast as $milestone)
                                        <div class="flex-1 p-2 bg-indigo-50/30 dark:bg-indigo-900/10 rounded-xl text-center">
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
                                            const wCtx = document.getElementById('growthWeightCanvasFull');
                                            const hCtx = document.getElementById('growthHeightCanvasFull');
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

                            <div class="p-6 mt-4 rounded-3xl bg-violet-600 text-white shadow-2xl shadow-violet-500/30 relative overflow-hidden group">
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <label class="text-[10px] font-black text-violet-200 uppercase tracking-widest">Total Amount</label>
                                        <div class="flex items-center gap-2">
                                            @if($isFollowUp)
                                                <div class="px-2 py-0.5 bg-emerald-500 rounded-md text-[9px] font-black uppercase tracking-widest text-white animate-bounce">FREE FOLLOW-UP</div>
                                            @endif
                                            <div class="px-2 py-0.5 bg-white/20 rounded-md text-[9px] font-black uppercase tracking-widest">REALTIME</div>
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-2 mb-2">
                                        <span class="text-xl font-black opacity-60 pb-1">₹</span>
                                        <input type="number" wire:model="fee" class="w-full bg-transparent border-none p-0 text-3xl font-black focus:ring-0 outline-none text-white transition-all">
                                    </div>
                                    @error('fee') <p class="text-[10px] font-bold text-white/80 mb-4">{{ $message }}</p> @enderror
                                    
                                    <div class="relative">
                                        <select wire:model="paymentStatus" class="w-full bg-white/10 hover:bg-white/20 text-white border-none rounded-xl px-4 py-3 text-xs font-bold transition-all outline-none mb-2">
                                            <option class="text-gray-900" value="Paid">Payment: Paid</option>
                                            <option class="text-gray-900" value="Partially Paid">Payment: Partially Paid</option>
                                            <option class="text-gray-900" value="Unpaid">Payment: Unpaid</option>
                                        </select>
                                        @error('paymentStatus') <p class="text-[10px] font-bold text-white/80 mt-2">{{ $message }}</p> @enderror

                                        @if($paymentStatus === 'Partially Paid')
                                            <input type="number" step="1" min="0" wire:model="amountPaid" class="w-full bg-white/10 hover:bg-white/20 text-white border-none rounded-xl px-4 py-3 text-xs font-bold transition-all outline-none mb-2" placeholder="Amount paid">
                                            @error('amountPaid') <p class="text-[10px] font-bold text-white/80 mt-2">{{ $message }}</p> @enderror
                                        @endif

                                        <select wire:model="paymentMode" class="w-full bg-white/10 hover:bg-white/20 text-white border-none rounded-xl px-4 py-3 text-xs font-bold transition-all outline-none">
                                            <option class="text-gray-900" value="Cash">Settlement: Physical Cash</option>
                                            <option class="text-gray-900" value="UPI">Settlement: Digital UPI</option>
                                            <option class="text-gray-900" value="Card">Settlement: Terminal Card</option>
                                        </select>
                                        @error('paymentMode') <p class="text-[10px] font-bold text-white/80 mt-2">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <svg class="absolute -right-8 -bottom-8 w-32 h-32 text-white/10 rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 12l11 10 11-10L12 2zm0 18.5L2.5 12 12 3.5l9.5 8.5L12 20.5z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Presentation -->
                    <div class="space-y-3">
                        <label class="text-tiny font-black text-gray-400 uppercase tracking-[0.2em] ml-2 flex items-center gap-2">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                              Notes / Symptoms
                        </label>
                        <textarea wire:model="notes" rows="3" class="w-full bg-gray-50 dark:bg-gray-900/50 border-2 border-transparent focus:border-indigo-500 rounded-ultra px-6 py-5 outline-none transition-all font-bold placeholder-gray-300 dark:placeholder-gray-700" placeholder="E.g. Fever, Headache, etc..."></textarea>
                    </div>

                    <!-- Action Interface -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-8 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="$dispatch('close-modal', { name: 'booking-modal' })" 
                                class="px-8 py-4 text-tiny font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Cancel
                        </button>
                        
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" 
                                    wire:click="book(false)" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 sm:flex-auto px-8 py-4 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-[1.5rem] text-tiny font-black uppercase tracking-[0.2em] transition-all disabled:opacity-50">
                                <span wire:loading.remove wire:target="book(false)">Save Only</span>
                                <span wire:loading wire:target="book(false)">Processing...</span>
                            </button>
                            <button type="button" 
                                    wire:click="book(true)" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 sm:flex-auto px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-tiny font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/30 transition-all disabled:opacity-50">
                                <span wire:loading.remove wire:target="book(true)">Save & Print</span>
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

</div>
