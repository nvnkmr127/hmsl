<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">IPD Vitals Chart</h3>
        @unless($admission->status === 'Discharged')
            <button wire:click="$toggle('showForm')" class="btn btn-primary text-xs">
                Record Vitals
            </button>
        @endunless
    </div>

    @if($showForm)
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl mb-4">
            <h4 class="font-bold text-gray-900 dark:text-white mb-3">New Vital Signs Entry</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">BP Systolic</label>
                    <input type="number" wire:model.live="bp_systolic" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="mmHg">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">BP Diastolic</label>
                    <input type="number" wire:model.live="bp_diastolic" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="mmHg">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Pulse (bpm)</label>
                    <input type="number" wire:model.live="pulse" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="bpm">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Temp (°F)</label>
                    <input type="number" step="0.1" wire:model.live="temperature" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="°F">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">SpO2 (%)</label>
                    <input type="number" wire:model.live="spo2" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="%">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Resp Rate</label>
                    <input type="number" wire:model.live="resp_rate" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="/min">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Weight (kg)</label>
                    <input type="number" step="0.1" wire:model.live="weight" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="kg">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Height (cm)</label>
                    <input type="number" step="0.1" wire:model.live="height" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="cm">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">BMI</label>
                    <input type="text" wire:model="bmi" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm bg-gray-100" readonly placeholder="Auto">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Pain Scale (0-10)</label>
                    <input type="number" wire:model.live="pain_scale" min="0" max="10" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="0-10">
                </div>
            </div>
            <div class="mt-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Notes</label>
                <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Additional observations..."></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="$toggle('showForm')" class="btn btn-secondary text-xs">Cancel</button>
                <button wire:click="save" class="btn btn-primary text-xs">Save Vitals</button>
            </div>
        </div>
    @endif

    @if($this->latestVital)
        <div class="p-4 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl mb-4">
            <h4 class="font-bold text-indigo-900 dark:text-indigo-200 mb-3">Latest Vitals ({{ $this->latestVital->recorded_at->format('d M Y, h:i A') }})</h4>
            <div class="grid grid-cols-3 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase mb-1">BP</p>
                    <p class="text-lg font-bold {{ $this->latestVital->isAbnormalBp() ? 'text-rose-600' : 'text-gray-900 dark:text-white' }}">
                        {{ $this->latestVital->bp ?? '-' }}
                    </p>
                    @if($this->latestVital->isAbnormalBp())
                        <span class="text-xs text-rose-500">Abnormal</span>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase mb-1">Pulse</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->latestVital->pulse ?? '-' }}</p>
                    <p class="text-xs text-gray-400">bpm</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase mb-1">Temp</p>
                    <p class="text-lg font-bold {{ $this->latestVital->isAbnormalTemperature() ? 'text-rose-600' : 'text-gray-900 dark:text-white' }}">
                        {{ $this->latestVital->temperature ? $this->latestVital->temperature . '°F' : '-' }}
                    </p>
                    @if($this->latestVital->isAbnormalTemperature())
                        <span class="text-xs text-rose-500">Abnormal</span>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase mb-1">SpO2</p>
                    <p class="text-lg font-bold {{ $this->latestVital->isAbnormalSpo2() ? 'text-rose-600' : 'text-gray-900 dark:text-white' }}">
                        {{ $this->latestVital->spo2 ? $this->latestVital->spo2 . '%' : '-' }}
                    </p>
                    @if($this->latestVital->isAbnormalSpo2())
                        <span class="text-xs text-rose-500">Low</span>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase mb-1">Weight</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->latestVital->weight ? $this->latestVital->weight . ' kg' : '-' }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($this->vitalsHistory->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Date/Time</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">BP</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Pulse</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Temp</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">SpO2</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">RR</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Weight</th>
                        <th class="text-left py-2 font-bold text-gray-500 uppercase text-xs">Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->vitalsHistory as $vital)
                        <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="py-2">{{ $vital->recorded_at->format('d M, h:i A') }}</td>
                            <td class="py-2 {{ $vital->isAbnormalBp() ? 'text-rose-600 font-bold' : '' }}">{{ $vital->bp ?? '-' }}</td>
                            <td class="py-2">{{ $vital->pulse ?? '-' }}</td>
                            <td class="py-2 {{ $vital->isAbnormalTemperature() ? 'text-rose-600 font-bold' : '' }}">{{ $vital->temperature ? $vital->temperature . '°F' : '-' }}</td>
                            <td class="py-2 {{ $vital->isAbnormalSpo2() ? 'text-rose-600 font-bold' : '' }}">{{ $vital->spo2 ? $vital->spo2 . '%' : '-' }}</td>
                            <td class="py-2">{{ $vital->resp_rate ?? '-' }}</td>
                            <td class="py-2">{{ $vital->weight ? $vital->weight . ' kg' : '-' }}</td>
                            <td class="py-2 text-gray-500">{{ $vital->recordedBy?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-sm">No vitals recorded yet.</p>
        </div>
    @endif
</div>
