<div>
    <x-page-header :title="'Patient Record: ' . $patient->full_name" :subtitle="'Past visits and payment details.'">
        <x-slot name="actions">
            <button onclick="window.print()" class="btn btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4" /></svg>
                Print Report
            </button>
            <a href="{{ route('counter.opd.index', ['patient_id' => $patient->id]) }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Create OP Visit
            </a>
            <a href="{{ route('counter.patients.index') }}" class="btn btn-ghost ring-1 ring-gray-200">
                Back to Patients
            </a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- LEFT SIDE: Main Clinical History (Longitudinal Data) -->
        <div class="lg:col-span-3 space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <!-- 1. Patient IDENTITY Hero -->
            <div class="card p-0 overflow-hidden border-0 shadow-2xl shadow-violet-100/50 dark:shadow-none bg-white dark:bg-gray-950 rounded-[2.5rem]">
                <div class="bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-800 text-white relative overflow-hidden">
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -left-20 -bottom-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 p-8">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                            <div class="flex items-center gap-6">
                                <div class="w-20 h-20 rounded-[2rem] bg-white/20 backdrop-blur-md flex items-center justify-center font-black text-3xl shadow-inner border border-white/20 shrink-0">
                                    {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h1 class="text-3xl font-black uppercase tracking-tight leading-tight mb-2">{{ $patient->full_name }}</h1>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-white/10 text-[10px] font-black uppercase tracking-widest border border-white/10">UHID: {{ $patient->uhid }}</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-white/10 text-[10px] font-black uppercase tracking-widest border border-white/10">{{ $patient->gender }} | {{ $patient->age }} Years</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-amber-400/20 text-amber-100 text-[10px] font-black uppercase tracking-widest border border-amber-400/20">Blood: {{ $patient->blood_group ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-4 md:gap-12">
                                <div class="border-l border-white/10 pl-6">
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-60 mb-1">Total Visits</p>
                                    <p class="text-xl font-black">{{ $visits->count() }}</p>
                                </div>
                                <div class="border-l border-white/10 pl-6">
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-60 mb-1">Last Seen</p>
                                    <p class="text-xl font-black">{{ $visits->first()?->consultation_date->format('d M, Y') ?: 'Never' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-white/10 grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest opacity-50 mb-1">Guardian</p>
                                <p class="text-[10px] font-bold">{{ $patient->father_name ?: 'None' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest opacity-50 mb-1">Contact</p>
                                <p class="text-[10px] font-bold">{{ $patient->phone ?: 'None' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest opacity-50 mb-1">Emergency</p>
                                <p class="text-[10px] font-bold text-amber-200">{{ $patient->emergency_contact_phone ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest opacity-50 mb-1">Allergies</p>
                                <p class="text-[10px] font-black text-red-300 truncate">{{ $patient->allergies ?: 'None Reported' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Clinical Timeline & Physiology -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                {{-- Timeline (Visual) --}}
                <div class="md:col-span-3 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-[0.2em] flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-violet-600 rounded-full"></span> Clinical History Timeline
                        </h3>
                    </div>
                    <div class="relative pl-11 py-2">
                        <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-violet-100 via-violet-200 to-transparent dark:from-violet-900/30 border-none"></div>
                        @forelse($timeline->take(10) as $event)
                            <div class="mb-8 last:mb-0 relative group">
                                <div class="absolute -left-[27px] top-1 w-8 h-8 rounded-xl bg-white dark:bg-gray-950 border-2 border-{{ $event->color }}-500 z-10 flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:bg-{{ $event->color }}-500 group-hover:text-white transition-all duration-300">
                                    @if($event->type == 'OP Visit')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @elseif($event->type == 'Admission')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @elseif($event->type == 'Billing')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @elseif($event->type == 'Discharge')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </div>
                                <div class="bg-white dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-800/50 shadow-sm transition-all duration-300">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-black text-{{ $event->color }}-600 uppercase">{{ $event->type }}</span>
                                        <span class="text-[10px] font-bold text-gray-400 tabular-nums">{{ $event->date->format('d M, Y') }}</span>
                                    </div>
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 leading-tight mb-1">{{ $event->title }}</h4>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium italic">"{{ $event->meta }}"</p>
                                        </div>
                                        @if($event->type == 'Billing' && isset($event->bill_id))
                                            <a href="{{ route('counter.bills.print', $event->bill_id) }}" target="_blank" class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-black uppercase">PDF</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-center text-gray-400 font-bold uppercase py-10">No history found</p>
                        @endforelse
                    </div>
                </div>

                {{-- Vitals (Side Table) --}}
                <div class="md:col-span-2 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-[0.2em] flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span> Vital Logs
                        </h3>
                    </div>
                    <div class="card p-0 overflow-hidden border border-gray-100 dark:border-gray-800/50 bg-white dark:bg-gray-950 rounded-3xl shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800/50">
                                <tr>
                                    <th class="px-4 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                    <th class="px-2 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">T/W</th>
                                    <th class="px-4 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">BP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-900/30">
                                @forelse($vitals->take(8) as $v)
                                    <tr class="hover:bg-emerald-50/30 transition-colors">
                                        <td class="px-4 py-4 text-[10px] font-bold text-gray-600 dark:text-gray-400">{{ $v->created_at->format('d/m H:i') }}</td>
                                        <td class="px-2 py-4 text-center">
                                            <p class="text-[10px] font-black text-gray-900 dark:text-white">{{ $v->temperature ?? '--' }}°F</p>
                                            <p class="text-[8px] text-gray-400">{{ $v->weight ?? '--' }}kg</p>
                                        </td>
                                        <td class="px-4 py-4 text-center text-[10px] font-black text-gray-900 dark:text-white">{{ $v->blood_pressure ?? '--' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-5 py-10 text-center text-[10px] font-black text-gray-400 uppercase">No records</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Immunization Tracker (Full Width Main) -->
            <div class="card p-0 overflow-hidden border border-gray-100 dark:border-gray-800/50 bg-white dark:bg-gray-950 rounded-[2.5rem] shadow-sm">
                <div class="p-8 pb-4 border-b border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-[0.2em] flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span> Immunization Tracking
                        </h3>
                    </div>
                    @php $totalCount = $allVaccines->count(); $percent = $totalCount > 0 ? round(($vaccinations->count() / $totalCount) * 100) : 0; @endphp
                    <div class="text-right">
                        <p class="text-xl font-black text-blue-600">{{ $percent }}%</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800/50 text-[9px] font-black text-gray-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4">Recommended Age</th>
                                <th class="px-8 py-4">Vaccine Name</th>
                                <th class="px-8 py-4 text-center">Status</th>
                                <th class="px-8 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-900/30">
                            @foreach($allVaccines as $v)
                                @php $given = $vaccinations->firstWhere('vaccine_id', $v->id); @endphp
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-8 py-4 text-[11px] font-black text-gray-800 dark:text-gray-100">{{ $v->recommended_age }}</td>
                                    <td class="px-8 py-4 text-[11px] font-black text-gray-900 dark:text-white">{{ $v->name }}</td>
                                    <td class="px-8 py-4 text-center">
                                        @if($given) <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase">Given</span>
                                        @else <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-400 text-[10px] font-black uppercase">Pending</span> @endif
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        @if(!$given) <button wire:click="recordVaccination({{ $v->id }}, '{{ date('Y-m-d') }}')" class="btn-xs px-3 py-1 bg-violet-600 text-white rounded-lg text-[10px] font-black uppercase">Record</button>
                                        @else <p class="text-[9px] text-gray-400 italic">Given {{ $given->date_given->format('d/m/Y') }}</p> @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Status & Administrative Sidebar -->
        <div class="lg:col-span-1 space-y-6 animate-in fade-in slide-in-from-right-4 duration-700 delay-150">
            
            {{-- 1. Alerts (Top Priority) --}}
            @if(count($alerts))
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <div class="p-4 rounded-2xl bg-{{ $alert['type'] }}-50 dark:bg-{{ $alert['type'] }}-900/10 border border-{{ $alert['type'] }}-100 dark:border-{{ $alert['type'] }}-800/30 flex items-start gap-4">
                            <div class="w-8 h-8 rounded-xl bg-{{ $alert['type'] }}-500 flex items-center justify-center text-white shrink-0 shadow-lg shadow-{{ $alert['type'] }}-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-{{ $alert['type'] }}-600 uppercase tracking-widest">{{ $alert['label'] }}</p>
                                <p class="text-[11px] font-bold text-gray-900 dark:text-white leading-tight mt-0.5">{{ $alert['msg'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 2. Today's Appointment Status --}}
            @php $today = $visits->firstWhere('consultation_date', date('Y-m-d')); @endphp
            @if($today)
                <div class="card p-6 bg-violet-600 text-white border-0 rounded-[2rem] shadow-xl shadow-violet-200/50 dark:shadow-none relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-70">Today's Token</p>
                                <p class="text-3xl font-black">#{{ str_pad($today->token_number, 2, '0', STR_PAD_LEFT) }}</p>
                            </div>
                            <div class="px-2.5 py-1 rounded-lg bg-white/20 text-[10px] font-black uppercase">{{ $today->payment_status }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-white/10 mb-4">
                            <div>
                                <p class="text-[8px] font-black uppercase opacity-60">Temp</p>
                                <p class="text-lg font-black tabular-nums">{{ $today->temperature ?? '--' }}°F</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase opacity-60">Weight</p>
                                <p class="text-lg font-black tabular-nums">{{ $today->weight ?? '--' }}kg</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-[8px] font-black uppercase opacity-60">Validity</p>
                            <p class="text-[9px] font-black uppercase">{{ $today->valid_upto->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. Billing & Invoices Center --}}
            <div class="card p-0 overflow-hidden bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-[2rem] shadow-sm">
                <div class="p-6 bg-gray-900 text-white">
                    <h3 class="text-[10px] font-black text-violet-400 uppercase tracking-widest mb-4">Ledger Summary</h3>
                    <div class="flex justify-between items-end">
                        <p class="text-[9px] font-black text-gray-500 uppercase tracking-widest leading-none">30 Day Rev</p>
                        <p class="text-2xl font-black leading-none">₹{{ number_format($billStats['thirty_days'], 0) }}</p>
                    </div>
                </div>
                <div class="p-2 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-[8px] font-black text-gray-400 uppercase border-b border-gray-50 dark:border-gray-900/50">
                            <tr>
                                <th class="px-4 py-3">Inv #</th>
                                <th class="px-4 py-3 text-right">PDF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-900/30">
                            @forelse($allBills->take(5) as $bill)
                                <tr class="hover:bg-violet-50/50">
                                    <td class="px-4 py-3">
                                        <p class="text-[10px] font-black">#{{ $bill->bill_number }}</p>
                                        <p class="text-xs text-gray-400 tabular-nums">₹{{ number_format($bill->total_amount, 0) }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('counter.bills.print', $bill->id) }}" target="_blank" class="p-2 rounded-lg bg-gray-50 text-gray-400 hover:bg-violet-600 hover:text-white transition-all inline-block">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-6 text-center text-[9px] font-black text-gray-400 uppercase">No Bills</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 4. Insurance & Administrative --}}
            <div class="card p-6 bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-[2rem] shadow-sm">
                <h3 class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div> Insurance
                </h3>
                @if($patient->insurance_provider)
                    <div class="space-y-3">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100">
                            <p class="text-[8px] font-black text-gray-400 uppercase mb-1">Provider</p>
                            <p class="text-[10px] font-black uppercase">{{ $patient->insurance_provider }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100">
                            <p class="text-[8px] font-black text-gray-400 uppercase mb-1">Policy #</p>
                            <p class="text-[10px] font-black">{{ $patient->insurance_policy }}</p>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl border-2 border-dashed border-gray-100 text-center">
                        <p class="text-[9px] font-black text-gray-400 uppercase leading-loose">Self Pay / Cash Only</p>
                    </div>
                @endif
            </div>

            {{-- 5. Care Tip --}}
            <div class="p-6 rounded-[2rem] bg-indigo-600 text-white relative overflow-hidden shadow-xl shadow-indigo-600/20">
                <p class="text-[9px] font-black uppercase tracking-widest text-indigo-100 mb-2">Care Tip</p>
                <p class="text-[11px] font-semibold leading-relaxed italic opacity-90">"Always verify dosages against the patient's age ({{ $patient->age }}Y) and latest recorded weight."</p>
            </div>
        </div>
    </div>
</div>
