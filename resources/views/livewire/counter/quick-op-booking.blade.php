<div @open-modal.window="if($event.detail.name === 'quick-op-modal') $nextTick(() => $el.querySelector('input')?.focus())">
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
                <div class="mb-8 relative">
                   <x-clinical.patient-strip :patient="$selectedPatient" size="md" />
                   @if($isFollowUp)
                       <div class="absolute -top-3 -right-3">
                           <span class="bg-emerald-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-lg border-2 border-white dark:border-gray-900 uppercase tracking-widest animate-pulse">
                               Follow-up Visit
                           </span>
                       </div>
                   @endif
                </div>

                @if($isFollowUp)
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
                                Dr. {{ $latestConsultation->doctor?->full_name ?? 'N/A' }}
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

                            <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">WT (kg)</label>
                                    <input type="number" step="0.1" wire:model.live.debounce.500ms="weight" placeholder="0.0" class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-transparent focus:border-indigo-500 rounded-2xl px-3 py-4 font-bold text-gray-900 dark:text-white outline-none transition-all text-sm" />
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
