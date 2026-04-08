<div>
    <x-page-header 
        title="Admitted Patients" 
        subtitle="Manage current patient stays, oversee bed occupancy, and coordinate discharges."
    >
        <x-slot name="actions">
            <a href="{{ route('counter.ipd.create') }}" class="btn btn-primary px-8 py-4 shadow-xl shadow-indigo-500/30 rounded-2xl group transition-all active:scale-95 flex items-center gap-3">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span class="font-black uppercase tracking-widest text-xs">New Admission</span>
            </a>
        </x-slot>
    </x-page-header>

    <div class="mt-10">
        <div class="mb-8 p-1 bg-white dark:bg-gray-900 rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 transition-all focus-within:shadow-indigo-500/10">
            <div class="flex flex-col lg:flex-row items-center gap-4 px-4 py-2">
                <div class="flex-1 relative group w-full">
                    <div class="absolute left-6 top-1/2 -translate-y-1/2 text-indigo-500 group-focus-within:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="SEARCH PATIENTS: NAME, ID OR MOBILE..." 
                        class="w-full bg-transparent border-none pl-14 pr-6 py-4 text-sm font-black tracking-widest text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 focus:ring-0 uppercase"
                    />
                </div>
                <div class="h-8 w-px bg-gray-100 dark:bg-gray-800 hidden lg:block"></div>
                <label class="flex items-center gap-3 px-6 cursor-pointer group whitespace-nowrap">
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" wire:model.live="showDischarged" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Show Discharged</span>
                </label>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-500/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-50 dark:border-gray-800/50">
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Patient Details</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">ID & Admission #</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Ward & Bed</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/30">
                        @forelse($admissions as $adm)
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 font-black text-lg group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500">
                                            {{ substr($adm->patient->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('counter.ipd.show', $adm->id) }}" class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight hover:text-indigo-600">{{ $adm->patient->full_name }}</a>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $adm->patient->gender }} · {{ $adm->patient->age }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <p class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ $adm->admission_number }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>
                                            <span class="text-[10px] font-black text-violet-500 uppercase tracking-widest">{{ $adm->patient->uhid }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                                            <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">{{ $adm->bed?->ward?->name ?? 'OPD' }}</p>
                                            <p class="text-[9px] font-bold text-emerald-400 uppercase opacity-60">Bed {{ $adm->bed?->bed_number ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @php 
                                        $statusConfig = match($adm->status) {
                                            'Admitted' => ['bg' => 'bg-violet-500', 'text' => 'text-violet-500', 'label' => 'ADMITTED'],
                                            'Discharged' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-500', 'label' => 'DISCHARGED'],
                                            default => ['bg' => 'bg-amber-500', 'text' => 'text-amber-500', 'label' => 'PENDING']
                                        };
                                    @endphp
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50 shadow-sm">
                                        <span class="relative flex h-2 w-2">
                                            @if($adm->status === 'Admitted')
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $statusConfig['bg'] }} opacity-75"></span>
                                            @endif
                                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $statusConfig['bg'] }}"></span>
                                        </span>
                                        <span class="text-[9px] font-black uppercase tracking-[0.2em] {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('billing.index', ['search' => $adm->patient->uhid]) }}" class="p-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-emerald-500/20" title="Collect Due">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </a>
                                        @if($adm->status === 'Admitted')
                                            <button wire:click="orderLabs({{ $adm->id }})" class="p-3 bg-sky-50 dark:bg-sky-950/30 text-sky-600 rounded-xl hover:bg-sky-600 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-sky-500/20" title="Order Labs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.673.337a4 4 0 01-2.574.344l-1.474-.411a5 5 0 00-3.578.176l-1.41.632" /></svg>
                                            </button>
                                            <button wire:click="dischargePatient({{ $adm->id }})" class="p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all group/btn shadow-sm hover:shadow-lg hover:shadow-rose-500/20" title="Discharge">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('discharge.summary', ['admission' => $adm->id]) }}" class="p-3 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-xl hover:bg-indigo-500 hover:text-white transition-all shadow-sm" title="Summary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('counter.patients.history', $adm->patient_id) }}" class="p-3 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-xl hover:bg-violet-600 hover:text-white transition-all shadow-sm" title="Clinical History">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800/50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-gray-100 dark:border-gray-800">
                                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </div>
                                        <p class="text-tiny font-black text-gray-400 uppercase tracking-[0.3em]">No Admissions Found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admissions->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-50 dark:border-gray-800">
                    {{ $admissions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Check-out Modal -->
    <x-modal name="ipd-discharge-modal" title="Patient Check-out" width="xl">
        <div class="p-8 space-y-8">
            <div class="flex items-center gap-4 p-5 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-2xl">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-900 dark:text-amber-200 uppercase tracking-widest">Wait!</h4>
                    <p class="text-[9px] text-amber-600 dark:text-amber-400/60 font-bold uppercase">Make sure all bills are cleared before checking out.</p>
                </div>
            </div>

            <div class="space-y-4" x-data="{ open: false, search: @entangle('dischargeNotes') }">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Follow-up Instructions & Notes</label>
                <div class="relative">
                    <textarea 
                        wire:model.live="dischargeNotes" 
                        @focus="open = true"
                        @click.away="open = false"
                        rows="4" 
                        class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-3xl px-6 py-5 outline-none transition-all font-bold text-gray-900 dark:text-white text-sm shadow-sm placeholder-gray-300 dark:placeholder-gray-700 ring-4 ring-gray-100/50 dark:ring-gray-900/50" 
                        placeholder="ENTER DISCHARGE SUMMARY OR SELECT BELOW..."
                    ></textarea>
                    
                    <div x-show="open" class="absolute z-50 left-0 right-0 mt-2 p-2 bg-white dark:bg-gray-900 rounded-2xl shadow-3xl border border-gray-100 dark:border-gray-800 max-h-48 overflow-y-auto custom-scrollbar">
                        @foreach($dischargeTemplates as $n)
                            <button 
                                type="button"
                                @click="search = search ? (search + '\n' + '{{ $n->content }}') : '{{ $n->content }}'; open = false"
                                class="w-full text-left px-4 py-3 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wide transition-colors"
                            >
                                {{ $n->content }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <button type="button" @click="$dispatch('close-modal', { name: 'ipd-discharge-modal' })" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Cancel</button>
                <button type="button" wire:click="confirmDischarge" class="px-10 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 transition-all active:scale-95">
                    Confirm Check-out
                </button>
            </div>
        </div>
    </x-modal>

    <x-modal name="ipd-lab-order-modal" title="Order Lab Tests" width="2xl">
        <div class="p-6 space-y-4">
            <div class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar pr-1">
                @foreach($labTests as $t)
                    <label class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/20">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" wire:model="selectedLabTests" value="{{ $t->id }}" class="checkbox checkbox-sm" />
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $t->name }}</p>
                                <p class="text-xs text-gray-500">₹ {{ number_format((float) $t->price, 2) }}</p>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Notes (Optional)</label>
                <textarea wire:model.live="labNotes" rows="2" class="w-full bg-gray-50 dark:bg-gray-950 border-2 border-transparent focus:border-indigo-500 rounded-3xl px-6 py-4 outline-none transition-all font-bold text-gray-900 dark:text-white text-sm shadow-sm placeholder-gray-300 dark:placeholder-gray-700 ring-4 ring-gray-100/50 dark:ring-gray-900/50"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="$dispatch('close-modal', { name: 'ipd-lab-order-modal' })" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Cancel</button>
                <button type="button" wire:click="confirmLabOrder" class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
                    Create Order
                </button>
            </div>
        </div>
    </x-modal>
</div>
