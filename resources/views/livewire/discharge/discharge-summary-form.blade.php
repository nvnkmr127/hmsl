<div>
    <x-page-header title="Discharge Summary" :subtitle="'Admission: ' . $admission->admission_number">
        <x-slot name="actions">
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary">
                Back to Admissions
            </a>
            @if($summary?->is_finalized)
                <a href="{{ route('discharge.summary.print', $admission->id) }}" target="_blank" class="btn btn-primary">
                    Print Summary
                </a>
            @endif
        </x-slot>
    </x-page-header>

    @if($summary?->is_finalized)
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-2xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="font-bold text-emerald-900 dark:text-emerald-200">Summary Finalized</p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-300">
                        Finalized on {{ $summary->finalized_at->format('d M Y, h:i A') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <x-card title="Patient Info">
                <x-patient-identity :patient="$admission->patient" :subtitle="$admission->admission_number" />
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ward/Bed:</span>
                        <span class="font-semibold">{{ $admission->bed?->ward?->name ?? 'N/A' }} / {{ $admission->bed?->bed_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Doctor:</span>
                        <span class="font-semibold">{{ $admission->doctor?->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Admission:</span>
                        <span class="font-semibold">{{ $admission->admission_date->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <x-badge :type="$admission->status === 'Admitted' ? 'success' : 'secondary'">{{ $admission->status }}</x-badge>
                    </div>
                </div>
            </x-card>

            @if($summary)
                <x-card title="Workflow" class="mt-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $summary->status === 'Draft' ? 'bg-gray-400' : 'bg-emerald-500' }}"></span>
                            <span class="text-sm">Draft</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $summary->status === 'Review' ? 'bg-amber-500' : ($summary->status === 'Finalized' ? 'bg-emerald-500' : 'bg-gray-200') }}"></span>
                            <span class="text-sm">Review</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $summary->status === 'Finalized' ? 'bg-emerald-500' : 'bg-gray-200' }}"></span>
                            <span class="text-sm">Finalized</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        @if($summary->status === 'Draft')
                            <button wire:click="submitForReview" class="btn btn-primary w-full">Submit for Review</button>
                        @elseif($summary->status === 'Review')
                            <button wire:click="returnToDraft" class="btn btn-secondary w-full mb-2">Return to Draft</button>
                            <button wire:click="finalize" class="btn btn-primary w-full">Finalize</button>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>

        <div class="lg:col-span-3">
            <x-card :noPad="true">
                <div class="border-b border-gray-100 dark:border-gray-800">
                    <nav class="flex gap-1 px-4">
                        <button wire:click="setTab('diagnosis')"
                                class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'diagnosis' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                            Diagnosis
                        </button>
                        <button wire:click="setTab('treatment')"
                                class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'treatment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                            Treatment
                        </button>
                        <button wire:click="setTab('medications')"
                                class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'medications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                            Medications
                        </button>
                        <button wire:click="setTab('condition')"
                                class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'condition' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                            Condition
                        </button>
                        <button wire:click="setTab('advice')"
                                class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'advice' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                            Advice
                        </button>
                        <button wire:click="setTab('followup')"
                                class="px-4 py-3 text-sm font-bold border-b-2 {{ $activeTab === 'followup' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                            Follow Up
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    @if($activeTab === 'diagnosis')
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Admission Diagnosis</label>
                                <textarea wire:model.live="admission_diagnosis" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $admission_diagnosis }}</textarea>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Final Diagnosis</label>
                                <textarea wire:model.live="final_diagnosis" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $final_diagnosis }}</textarea>
                            </div>
                            <button wire:click="saveSection('diagnosis')" class="btn btn-primary" @unless($summary?->is_finalized) @endunless>
                                Save Diagnosis
                            </button>
                        </div>
                    @endif

                    @if($activeTab === 'treatment')
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Treatment Summary</label>
                                <textarea wire:model.live="treatment_summary" rows="4" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $treatment_summary }}</textarea>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Procedures Done</label>
                                <textarea wire:model.live="procedures_done" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $procedures_done }}</textarea>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Investigations Summary</label>
                                <textarea wire:model.live="investigations_summary" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $investigations_summary }}</textarea>
                            </div>
                            <button wire:click="saveSection('treatment')" class="btn btn-primary">
                                Save Treatment
                            </button>
                        </div>
                    @endif

                    @if($activeTab === 'medications')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-gray-900 dark:text-white">Medications on Discharge</h4>
                                @unless($summary?->is_finalized)
                                    <div class="flex gap-2">
                                        <button wire:click="importFromMedicationChart" class="btn btn-secondary text-xs">
                                            Import from Chart
                                        </button>
                                        <button wire:click="$toggle('showMedForm')" class="btn btn-primary text-xs">
                                            Add Medication
                                        </button>
                                    </div>
                                @endunless
                            </div>

                            @if($showMedForm && !$summary?->is_finalized)
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Medicine Name</label>
                                            <input type="text" wire:model="newMedName" class="w-full rounded-lg border-gray-200">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Dosage</label>
                                            <input type="text" wire:model="newMedDosage" class="w-full rounded-lg border-gray-200" placeholder="e.g., 500mg">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Frequency</label>
                                            <select wire:model="newMedFrequency" class="w-full rounded-lg border-gray-200">
                                                <option value="">Select</option>
                                                <option>OD</option>
                                                <option>BD</option>
                                                <option>TDS</option>
                                                <option>QID</option>
                                                <option>SOS</option>
                                                <option>PRN</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Duration</label>
                                            <input type="text" wire:model="newMedDuration" class="w-full rounded-lg border-gray-200" placeholder="e.g., 5 days">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Route</label>
                                            <select wire:model="newMedRoute" class="w-full rounded-lg border-gray-200">
                                                <option>Oral</option>
                                                <option>IV</option>
                                                <option>IM</option>
                                                <option>SC</option>
                                                <option>Inhalation</option>
                                                <option>Topical</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-gray-500 uppercase">Instructions</label>
                                        <input type="text" wire:model="newMedInstructions" class="w-full rounded-lg border-gray-200">
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="$toggle('showMedForm')" class="btn btn-secondary">Cancel</button>
                                        <button wire:click="addMedication" class="btn btn-primary">Add</button>
                                    </div>
                                </div>
                            @endif

                            @if($summary?->medications && $summary->medications->count() > 0)
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Medicine</th>
                                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Dosage</th>
                                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Frequency</th>
                                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Duration</th>
                                            <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Route</th>
                                            @unless($summary?->is_finalized)
                                                <th class="text-right py-2"></th>
                                            @endunless
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary->medications as $med)
                                            <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                                <td class="py-2 font-semibold">{{ $med->medicine_name }}</td>
                                                <td class="py-2">{{ $med->dosage ?? '-' }}</td>
                                                <td class="py-2">{{ $med->frequency ?? '-' }}</td>
                                                <td class="py-2">{{ $med->duration ?? '-' }}</td>
                                                <td class="py-2">{{ $med->route ?? '-' }}</td>
                                                @unless($summary?->is_finalized)
                                                    <td class="py-2 text-right">
                                                        <button wire:click="removeMedication({{ $med->id }})" class="text-rose-500 hover:text-rose-700">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </td>
                                                @endunless
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-500 text-center py-8">No medications added yet.</p>
                            @endif
                        </div>
                    @endif

                    @if($activeTab === 'condition')
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Condition at Discharge</label>
                                <select wire:model.live="condition_at_discharge" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'disabled' : '' }}>
                                    <option value="">Select Condition</option>
                                    @foreach(['Stable', 'Improved', 'Critical', 'Referred', 'Expired', 'LAMA'] as $condition)
                                        <option value="{{ $condition }}">{{ $condition }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Condition Notes</label>
                                <textarea wire:model.live="condition_notes" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $condition_notes }}</textarea>
                            </div>
                            <button wire:click="saveSection('condition')" class="btn btn-primary">
                                Save Condition
                            </button>
                        </div>
                    @endif

                    @if($activeTab === 'advice')
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">General Advice</label>
                                <textarea wire:model.live="general_advice" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $general_advice }}</textarea>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Diet Advice</label>
                                <textarea wire:model.live="diet_advice" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $diet_advice }}</textarea>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Activity Advice</label>
                                <textarea wire:model.live="activity_advice" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $activity_advice }}</textarea>
                            </div>
                            <button wire:click="saveSection('advice')" class="btn btn-primary">
                                Save Advice
                            </button>
                        </div>
                    @endif

                    @if($activeTab === 'followup')
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Follow-up Date</label>
                                <input type="date" wire:model.live="follow_up_date" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 block">Follow-up Notes</label>
                                <textarea wire:model.live="follow_up_notes" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700" {{ $summary?->is_finalized ? 'readonly' : '' }}>{{ $follow_up_notes }}</textarea>
                            </div>
                            <button wire:click="saveSection('followup')" class="btn btn-primary">
                                Save Follow Up
                            </button>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</div>
