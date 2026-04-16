<div>
    @if(!$doctor)
        <div class="p-10 text-center rounded-mega bg-gray-50/50 dark:bg-gray-950/20 border-2 border-dashed border-gray-200 dark:border-gray-800">
            <div class="mx-auto w-20 h-20 bg-violet-100 dark:bg-violet-900/30 rounded-3xl flex items-center justify-center text-violet-600 mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Doctor Profile Missing</h2>
            <p class="text-gray-500 max-w-sm mx-auto font-medium">Your user account is not linked to a doctor profile. Please contact the administrator to assign your profile.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-140px)]">
            <!-- Left Side: Queue -->
            <div class="lg:col-span-1 flex flex-col h-full overflow-hidden">
                <div class="bg-white dark:bg-gray-950 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col h-full">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/10">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest">Today's Queue</h3>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-gray-50 dark:divide-gray-900">
                        @forelse($queue as $item)
                            <button 
                                wire:click="selectConsultation({{ $item->id }})"
                                class="w-full text-left p-5 transition-all relative group {{ $selectedConsultation && $selectedConsultation->id == $item->id ? 'bg-violet-600' : 'hover:bg-violet-50 dark:hover:bg-violet-900/10' }}"
                            >
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-tiny font-black uppercase tracking-widest {{ $selectedConsultation && $selectedConsultation->id == $item->id ? 'text-violet-200' : 'text-gray-400' }}">
                                        Token #{{ str_pad($item->token_number, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter {{ $item->status === 'Ongoing' ? 'bg-sky-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                                        {{ $item->status === 'Ongoing' ? 'With Doctor' : 'Waiting' }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-sm uppercase tracking-tight {{ $selectedConsultation && $selectedConsultation->id == $item->id ? 'text-white' : 'text-gray-900 dark:text-white' }}">
                                    {{ $item->patient->full_name }}
                                </h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-tiny font-bold uppercase {{ $selectedConsultation && $selectedConsultation->id == $item->id ? 'text-violet-200/70' : 'text-gray-400' }}">
                                        {{ $item->patient->age }} | {{ $item->patient->gender }}
                                    </span>
                                </div>
                            </button>
                        @empty
                            <div class="p-10 text-center opacity-40">
                                <p class="text-tiny font-black uppercase tracking-widest italic">Queue is empty</p>
                            </div>
                        @endforelse

                        @if($completed->count())
                            <div class="p-4 bg-gray-50 dark:bg-gray-950 border-y border-gray-100 dark:border-gray-800">
                                <p class="text-tiny font-black text-gray-400 uppercase tracking-widest">Recently Completed</p>
                            </div>
                            @foreach($completed as $c)
                                <div class="p-4 opacity-50 grayscale">
                                    <p class="text-dense font-bold text-gray-400 tracking-tighter uppercase">Token #{{ $c->token_number }}</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase">{{ $c->patient->full_name }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Side: Selected Patient Details -->
            <div class="lg:col-span-3">
                @if($selectedConsultation)
                    <div class="h-full flex flex-col bg-slate-50/50 dark:bg-gray-900/10 rounded-mega border border-gray-100 dark:border-gray-800 shadow-xl overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500">
                        
                        <!-- Top Bar: Quick Actions & Profile -->
                        <div class="p-8 bg-white dark:bg-gray-950 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                            <div class="flex items-center gap-6">
                                <div class="w-16 h-16 rounded-[1.5rem] bg-violet-600 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-violet-500/20">
                                    {{ strtoupper(substr($selectedConsultation->patient->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h2 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight leading-none mb-1">{{ $selectedConsultation->patient->full_name }}</h2>
                                    <div class="flex items-center gap-4">
                                        <span class="text-tiny font-black text-violet-600 uppercase tracking-[0.2em]">{{ $selectedConsultation->patient->age }} | {{ $selectedConsultation->patient->gender }}</span>
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span class="text-tiny font-black text-gray-400 uppercase tracking-widest">{{ $selectedConsultation->patient->uhid }}</span>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="completeAndNext" class="btn btn-primary px-10 py-5 rounded-3xl shadow-2xl shadow-violet-500/30 text-lg font-black uppercase tracking-[0.1em] flex items-center gap-3 group ring-8 ring-violet-600/5">
                                <span>Finish & Next</span>
                                <svg class="w-6 h-6 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </button>
                            <a href="{{ route('counter.ipd.create', ['patient_id' => $selectedConsultation->patient_id]) }}" class="btn btn-secondary px-6 py-5 rounded-3xl shadow-xl text-sm font-black uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Admit Patient
                            </a>
                        </div>

                        <!-- Structured Clinical View -->
                        <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
                            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                                
                                <!-- Main Clinical Column (2/3) -->
                                <div class="xl:col-span-2 space-y-8">
                                    
                                    {{-- B. Current Visit (OP Details) --}}
                                    <div class="grid grid-cols-4 gap-6">
                                        <div class="p-6 bg-white dark:bg-gray-950 rounded-ultra border border-gray-100 dark:border-gray-800 shadow-sm">
                                            <p class="text-dense font-black text-orange-500 uppercase tracking-widest mb-2">Weight</p>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $selectedConsultation->weight ?? '--' }}</span>
                                                <span class="text-tiny font-bold text-gray-400 uppercase">kg</span>
                                            </div>
                                        </div>
                                        <div class="p-6 bg-white dark:bg-gray-950 rounded-ultra border border-gray-100 dark:border-gray-800 shadow-sm">
                                            <p class="text-dense font-black text-blue-500 uppercase tracking-widest mb-2">Temperature</p>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $selectedConsultation->temperature ?? '--' }}</span>
                                                <span class="text-tiny font-bold text-gray-400 uppercase">°F</span>
                                            </div>
                                        </div>
                                        <div class="p-6 bg-white dark:bg-gray-950 rounded-ultra border border-gray-100 dark:border-gray-800 shadow-sm">
                                            <p class="text-dense font-black text-emerald-500 uppercase tracking-widest mb-2">Token</p>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-3xl font-black text-gray-900 dark:text-white">#{{ $selectedConsultation->token_number }}</span>
                                            </div>
                                        </div>
                                        <div class="p-6 bg-white dark:bg-gray-950 rounded-ultra border border-violet-200 dark:border-violet-900/50 shadow-lg shadow-violet-500/5">
                                            <p class="text-dense font-black text-violet-600 uppercase tracking-widest mb-2">Discount (₹)</p>
                                            <div class="flex items-baseline gap-1">
                                                <input type="number" wire:model.live.debounce.500ms="discount" class="w-full bg-transparent border-0 p-0 text-3xl font-black text-violet-600 focus:ring-0 placeholder-violet-200" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- C. Timeline --}}
                                    <div class="space-y-4">
                                        <h3 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-[0.2em] px-2">Clinical Timeline</h3>
                                        <div class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gray-200/50 dark:before:bg-gray-800/50">
                                            @foreach($timeline as $event)
                                                <div class="relative flex items-center gap-8 group">
                                                    <div class="absolute left-0 w-10 h-10 rounded-2xl bg-white dark:bg-gray-950 border-2 border-{{ $event->color }}-500 z-10 flex items-center justify-center shadow-sm">
                                                        <div class="w-2.5 h-2.5 rounded-full bg-{{ $event->color }}-500"></div>
                                                    </div>
                                                    <div class="flex-1 ml-12 py-3 px-6 bg-white/50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 hover:border-violet-200 transition-colors">
                                                        <div class="flex items-center gap-3 mb-1">
                                                            <span class="text-dense font-black text-gray-400 uppercase tracking-widest">{{ $event->date->format('d M, Y') }}</span>
                                                            <span class="text-dense font-black text-{{ $event->color }}-600 uppercase tracking-widest">{{ $event->type }}</span>
                                                        </div>
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight leading-none">{{ $event->title }}</h4>
                                                        <p class="text-tiny text-gray-500 font-medium mt-1 truncate italic">"{{ $event->meta }}"</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Digital Prescription Entry --}}
                                    <div class="p-8 rounded-mega bg-violet-600 text-white relative overflow-hidden flex items-center justify-between group shadow-2xl shadow-violet-500/20">
                                        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                                        
                                        <div class="flex items-center gap-10 relative z-10">
                                            <div class="w-16 h-16 rounded-[1.2rem] bg-white text-violet-600 flex items-center justify-center shadow-xl">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.673.337a4 4 0 01-2.574.344l-1.474-.411a5 5 0 00-3.578.176l-1.41.632"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xl font-black uppercase tracking-tight mb-1">Electronic Prescription</h4>
                                                <p class="text-xs font-medium text-white/70 leading-relaxed uppercase tracking-widest">Digital records for pharmacy & history</p>
                                            </div>
                                        </div>

                                        <div class="relative z-10 flex items-center gap-3">
                                            <button type="button"
                                                    @click="$dispatch('open-lab-order', { consultationId: {{ $selectedConsultation->id }} })"
                                                    class="px-8 py-4 bg-white/20 text-white rounded-[1.2rem] font-black text-xs uppercase tracking-widest hover:bg-white/30 transition-colors shadow-xl">
                                                Order Labs
                                            </button>
                                            <button type="button"
                                                    @click="$dispatch('open-prescription', { consultationId: {{ $selectedConsultation->id }} })"
                                                    class="px-8 py-4 bg-white text-violet-600 rounded-[1.2rem] font-black text-xs uppercase tracking-widest hover:scale-105 transition-transform shadow-xl">
                                                Add Medications
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Support Column (1/3) -->
                                <div class="space-y-8">
                                    
                                    {{-- G. Alerts --}}
                                    @if(count($alerts))
                                        <div class="space-y-3">
                                            @foreach($alerts as $alert)
                                                <div class="p-5 rounded-ultra bg-{{ $alert['type'] }}-50 dark:bg-{{ $alert['type'] }}-900/10 border border-{{ $alert['type'] }}-100 dark:border-{{ $alert['type'] }}-800/30 flex items-start gap-4">
                                                    <div class="w-10 h-10 rounded-2xl bg-{{ $alert['type'] }}-500 flex items-center justify-center text-white shrink-0 shadow-lg shadow-{{ $alert['type'] }}-500/20">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-dense font-black text-{{ $alert['type'] }}-600 uppercase tracking-widest mb-0.5">{{ $alert['label'] }}</p>
                                                        <p class="text-xs font-bold text-gray-900 dark:text-white leading-tight uppercase tracking-tighter">{{ $alert['msg'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Load Later: Billing & Insurance --}}
                                    <livewire:doctor.patient-support-insights :patientId="$selectedConsultation->patient_id" :key="'support-'.$selectedConsultation->id" lazy="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center bg-gray-50/50 dark:bg-gray-950/10 rounded-[3.5rem] border-4 border-dashed border-gray-100 dark:border-gray-800 p-10 text-center animate-in fade-in duration-700">
                        <div class="w-32 h-32 bg-white dark:bg-gray-900 rounded-mega flex items-center justify-center text-gray-200 mb-10 shadow-inner">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-4">Patient Queue Ready</h2>
                        <p class="text-gray-400 font-bold text-sm uppercase tracking-widest max-w-sm">Select any patient from the queue to start their clinical consultation.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <livewire:doctor.prescription-editor />
    <livewire:doctor.lab-order-composer />
</div>
