<div>
    <div class="border-b border-gray-100 dark:border-gray-800 mb-6">
        <nav class="flex gap-1 -mb-px">
            <button wire:click="setTab('vitals')" class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'vitals' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Vitals
            </button>
            <button wire:click="setTab('history')" class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                History
            </button>
            <button wire:click="setTab('examination')" class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'examination' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Examination
            </button>
            <button wire:click="setTab('diagnosis')" class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'diagnosis' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Diagnosis
            </button>
            <button wire:click="setTab('prescription')" class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'prescription' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Prescription
            </button>
        </nav>
    </div>

    @if($activeTab === 'vitals')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">BP Systolic (mmHg)</label>
                <input type="number" wire:model.live="bp_systolic" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="120">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">BP Diastolic (mmHg)</label>
                <input type="number" wire:model.live="bp_diastolic" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="80">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Pulse (bpm)</label>
                <input type="number" wire:model.live="pulse" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="72">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Temperature (°F)</label>
                <input type="number" step="0.1" wire:model.live="temperature" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="98.6">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">SpO2 (%)</label>
                <input type="number" wire:model.live="spo2" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="98">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Weight (kg)</label>
                <input type="number" step="0.1" wire:model.live="weight" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="70">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Height (cm)</label>
                <input type="number" step="0.1" wire:model.live="height" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="170">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Resp Rate (/min)</label>
                <input type="number" wire:model.live="resp_rate" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="16">
            </div>
        </div>
        <button wire:click="saveVitals" class="btn btn-primary mt-4">Save Vitals</button>
    @endif

    @if($activeTab === 'history')
        <div class="space-y-4">
            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Chief Complaints</label>
                <div class="flex gap-2 mb-2">
                    <input type="text" wire:model.live="newComplaint" class="flex-1 rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Add complaint...">
                    <button wire:click="addComplaint" class="btn btn-secondary text-xs">Add</button>
                </div>
                @if(count($chief_complaints))
                    <div class="flex flex-wrap gap-2">
                        @foreach($chief_complaints as $index => $complaint)
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 rounded-full text-sm">
                                {{ $complaint }}
                                <button wire:click="removeComplaint({{ $index }})" class="text-indigo-400 hover:text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">History of Present Illness</label>
                <textarea wire:model.live="history_of_present_illness" rows="4" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Detailed history of present illness..."></textarea>
            </div>

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Past History</label>
                <textarea wire:model.live="past_history" rows="3" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Previous illnesses, surgeries, hospitalizations..."></textarea>
            </div>

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Personal History</label>
                <textarea wire:model.live="personal_history" rows="3" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Habits, allergies, medications..."></textarea>
            </div>

            <button wire:click="saveComplaints" class="btn btn-primary">Save History</button>
        </div>
    @endif

    @if($activeTab === 'examination')
        <div class="space-y-4">
            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">General Examination</label>
                <textarea wire:model.live="general_examination" rows="4" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="General appearance, consciousness, etc..."></textarea>
            </div>

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Systemic Examination</label>
                <textarea wire:model.live="systemic_examination" rows="4" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="CVS, RS, CNS, P/A..."></textarea>
            </div>

            <div>
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Other Findings</label>
                <textarea wire:model.live="examination_findings" rows="3" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Additional examination findings..."></textarea>
            </div>

            <button wire:click="saveExamination" class="btn btn-primary">Save Examination</button>
        </div>
    @endif

    @if($activeTab === 'diagnosis')
        <div class="space-y-4">
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                <h4 class="font-bold text-gray-900 dark:text-white mb-3">Add Diagnosis</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Diagnosis Name</label>
                        <input type="text" wire:model.live="diagnosis_name" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., Type 2 Diabetes Mellitus">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">ICD Code (optional)</label>
                        <input type="text" wire:model.live="icd_code" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="e.g., E11.9">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Type</label>
                        <select wire:model.live="diagnosis_type" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                            <option value="Primary">Primary</option>
                            <option value="Secondary">Secondary</option>
                            <option value="Comorbidity">Comorbidity</option>
                        </select>
                    </div>
                </div>
                <button wire:click="addDiagnosis" class="btn btn-primary text-xs">Add Diagnosis</button>
            </div>

            @if(count($diagnoses))
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Type</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Diagnosis</th>
                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">ICD</th>
                            <th class="text-right py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diagnoses as $diag)
                            <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                <td class="py-2">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $diag['type'] === 'Primary' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $diag['type'] }}
                                    </span>
                                </td>
                                <td class="py-2 font-semibold">{{ $diag['diagnosis_name'] }}</td>
                                <td class="py-2 text-gray-500">{{ $diag['icd_code'] ?? '-' }}</td>
                                <td class="py-2 text-right">
                                    <button wire:click="removeDiagnosis({{ $diag['id'] }})" class="text-rose-500 hover:text-rose-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if($activeTab === 'prescription')
        <livewire:opd.prescription-editor :consultation="$consultation" />
    @endif
</div>
