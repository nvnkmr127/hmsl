<div>
    <x-page-header :title="'Patient History: ' . $patient->full_name" subtitle="All patient records in one place" :back="route('counter.patients.index')" backLabel="Patients">
        <x-slot:actions>
            @can('view opd')
            <a href="{{ route('counter.opd.index', ['patient_id' => $patient->id]) }}" class="btn btn-primary">New OP Token</a>
            @endcan
            <button wire:click="export('payments')" wire:loading.attr="disabled" class="btn btn-secondary">
                <span wire:loading.remove wire:target="export('payments')">Export Payments</span>
                <span wire:loading wire:target="export('payments')">Exporting...</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-card title="Patient Details">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">UHID</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $patient->uhid }}</p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Contact</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $patient->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Gender / Age</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $patient->gender ?? '—' }} · {{ $patient->age ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Blood Group</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $patient->blood_group ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Allergies</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $patient->allergies ?: 'None' }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Insurance">
            @if($patient->insurance_provider || $patient->insurance_policy || $patient->insurance_validity)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Provider</p>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $patient->insurance_provider ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Policy</p>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $patient->insurance_policy ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Validity</p>
                        <p class="font-bold text-gray-900 dark:text-white">
                            {{ $patient->insurance_validity ? \Illuminate\Support\Carbon::parse($patient->insurance_validity)->format('d M Y') : '—' }}
                        </p>
                    </div>
                </div>
            @else
                <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                    No insurance information.
                </div>
            @endif
        </x-card>

        <x-card title="Alerts">
            @if(count($alerts))
                <div class="space-y-2">
                    @foreach($alerts as $alert)
                        <div class="p-3 rounded-2xl bg-{{ $alert['type'] }}-50 dark:bg-{{ $alert['type'] }}-900/10 border border-{{ $alert['type'] }}-100 dark:border-{{ $alert['type'] }}-800/30">
                            <p class="text-xs font-black uppercase tracking-widest text-{{ $alert['type'] }}-700 dark:text-{{ $alert['type'] }}-300">{{ $alert['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $alert['msg'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                    No alerts.
                </div>
            @endif
        </x-card>
    </div>

    <div class="overflow-x-auto mb-4">
        <div class="inline-flex gap-2 p-1 rounded-2xl bg-gray-100/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
            <button wire:click="$set('tab','overview')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'overview' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Overview
            </button>
            <button wire:click="$set('tab','treatment')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'treatment' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Treatment <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['treatments'] }}</span>
            </button>
            <button wire:click="$set('tab','visits')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'visits' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                OP Visits <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['visits'] }}</span>
            </button>
            <button wire:click="$set('tab','appointments')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'appointments' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Appointments <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['appointments'] }}</span>
            </button>
            <button wire:click="$set('tab','admissions')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'admissions' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Admissions <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['admissions'] }}</span>
            </button>
            <button wire:click="$set('tab','discharges')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'discharges' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Discharges <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['discharges'] }}</span>
            </button>
            <button wire:click="$set('tab','prescriptions')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'prescriptions' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Medications <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['prescriptions'] }}</span>
            </button>
            <button wire:click="$set('tab','labs')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'labs' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Diagnostics <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['labs'] }}</span>
            </button>
            <button wire:click="$set('tab','billing')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'billing' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Billing <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['bills'] }}</span>
            </button>
            <button wire:click="$set('tab','payments')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'payments' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Payments
            </button>
            <button wire:click="$set('tab','vitals')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'vitals' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Vitals <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['vitals'] }}</span>
            </button>
            <button wire:click="$set('tab','vaccinations')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ $tab === 'vaccinations' ? 'bg-white dark:bg-gray-950 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400' }}">
                Vaccines <span class="ml-2 text-[10px] font-black text-gray-400">{{ $counts['vaccinations'] }}</span>
            </button>
        </div>
    </div>

    @if($tab !== 'overview' && $tab !== 'vaccinations' && $tab !== 'treatment')
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <x-form.input wire:model.live.debounce.300ms="search" placeholder="Search…" id="patient-history-search" />
                </div>
                <div class="md:col-span-2">
                    <x-form.input wire:model.live="dateFrom" id="patient-history-date-from" type="date" />
                </div>
                <div class="md:col-span-2">
                    <x-form.input wire:model.live="dateTo" id="patient-history-date-to" type="date" />
                </div>
                <div class="md:col-span-3">
                    @if(in_array($tab, ['visits','appointments']))
                        <select wire:model.live="status" class="w-full px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    @elseif(in_array($tab, ['billing','payments']))
                        <select wire:model.live="status" class="w-full px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">All Payments</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partially Paid">Partially Paid</option>
                        </select>
                    @elseif(in_array($tab, ['admissions']))
                        <select wire:model.live="status" class="w-full px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">All Status</option>
                            <option value="Admitted">Admitted</option>
                            <option value="Discharged">Discharged</option>
                        </select>
                    @elseif(in_array($tab, ['labs']))
                        <select wire:model.live="status" class="w-full px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Collected">Collected</option>
                            <option value="Completed">Completed</option>
                        </select>
                    @endif
                </div>
            </div>
        </x-card>
    @endif

    <div wire:loading class="p-8 text-center text-xs font-black uppercase tracking-widest text-gray-400">
        Loading…
    </div>

    @if($tab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card title="Summary">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OP Visits</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $counts['visits'] }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Admissions</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $counts['admissions'] }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Bills</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $counts['bills'] }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">30 Day Spend</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">₹{{ number_format($billStats['thirty_days'], 2) }}</p>
                    </div>
                </div>
            </x-card>

            <x-card title="Latest OP Visits">
                <div class="space-y-3">
                    @forelse($overview['latestVisits'] as $v)
                        <div class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-gray-900 dark:text-white truncate">Token #{{ $v->token_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $v->consultation_date?->format('d M Y') }} · {{ $v->doctor?->full_name }}</p>
                            </div>
                            <a target="_blank" href="{{ route('counter.opd.print', ['id' => $v->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                        </div>
                    @empty
                        <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                            No OP visits.
                        </div>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Latest Bills">
                <div class="space-y-3">
                    @forelse($overview['latestBills'] as $b)
                        <div class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-gray-900 dark:text-white truncate">{{ $b->bill_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $b->created_at?->format('d M Y') }} · ₹{{ number_format($b->total_amount, 2) }} · {{ $b->payment_status }}</p>
                            </div>
                            <a target="_blank" href="{{ route('billing.bills.print', ['bill' => $b->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                        </div>
                    @empty
                        <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                            No bills.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    @endif

    @if($tab === 'treatment')
        <x-card title="Treatment History">
            <x-slot:action>
                <button wire:click="export('treatment')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('treatment')">Export CSV</span>
                    <span wire:loading wire:target="export('treatment')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <x-card title="OP Notes">
                    <div class="space-y-3">
                        @forelse($treatmentPreview['visits'] as $v)
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5">
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $v->consultation_date?->format('d M Y') }} · Token #{{ $v->token_number }}</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $v->doctor?->full_name ?? '—' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 whitespace-pre-line">{{ $v->notes ?: '—' }}</p>
                            </div>
                        @empty
                            <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                                No OP notes.
                            </div>
                        @endforelse
                    </div>
                </x-card>

                <x-card title="Prescriptions">
                    <div class="space-y-3">
                        @forelse($treatmentPreview['prescriptions'] as $p)
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5">
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $p->created_at?->format('d M Y') }}</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $p->doctor?->full_name ?? '—' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ $p->diagnosis ?: '—' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 whitespace-pre-line">{{ $p->advice ?: '—' }}</p>
                                <div class="mt-3">
                                    <a target="_blank" href="{{ route('counter.prescriptions.print', ['id' => $p->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                                No prescriptions.
                            </div>
                        @endforelse
                    </div>
                </x-card>

                <x-card title="IPD Notes">
                    <div class="space-y-3">
                        @forelse($treatmentPreview['admissions'] as $a)
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5">
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $a->admission_date?->format('d M Y') }} · {{ $a->admission_number }}</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $a->doctor?->full_name ?? '—' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 whitespace-pre-line">{{ $a->notes ?: ($a->reason_for_admission ?: '—') }}</p>
                                @if($a->discharge_date)
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('discharge.summary', ['admission' => $a->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Summary</a>
                                        <a target="_blank" href="{{ route('discharge.print', ['admission' => $a->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-6 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                                No IPD notes.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </x-card>
    @endif

    @if($tab === 'billing')
        <x-card title="Billing Records">
            <x-slot:action>
                <button wire:click="export('bills')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('bills')">Export CSV</span>
                    <span wire:loading wire:target="export('bills')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['bills'] as $bill)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 truncate">{{ $bill->bill_number }}</p>
                                <p class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($bill->total_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bill->created_at?->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black uppercase tracking-widest text-gray-400">{{ $bill->payment_method ?? '—' }}</p>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mt-1">{{ $bill->payment_status }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <a target="_blank" href="{{ route('billing.bills.print', ['bill' => $bill->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No billing records.</div>
                @endforelse
            </div>

            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Bill No</x-table.th>
                            <x-table.th>Date</x-table.th>
                            <x-table.th class="text-right">Total</x-table.th>
                            <x-table.th>Method</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['bills'] as $bill)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $bill->bill_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $bill->created_at?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white text-right">₹{{ number_format($bill->total_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $bill->payment_method ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">{{ $bill->payment_status }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a target="_blank" href="{{ route('billing.bills.print', ['bill' => $bill->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="6" message="No billing records." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>

            @if($datasets['bills'])
                <div class="mt-4">
                    {{ $datasets['bills']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'visits')
        <x-card title="Outpatient Visits">
            <x-slot:action>
                <button wire:click="export('visits')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('visits')">Export CSV</span>
                    <span wire:loading wire:target="export('visits')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['visits'] as $v)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-gray-900 dark:text-white truncate">Token #{{ $v->token_number }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $v->consultation_date?->format('d M Y') }} · {{ $v->doctor?->full_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $v->status }} · {{ $v->payment_status }}</p>
                            </div>
                            <a target="_blank" href="{{ route('counter.opd.print', ['id' => $v->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No OP visits.</div>
                @endforelse
            </div>

            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Token</x-table.th>
                            <x-table.th>Date</x-table.th>
                            <x-table.th>Doctor</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th>Payment</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['visits'] as $v)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm font-black text-indigo-600 dark:text-indigo-400">#{{ $v->token_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $v->consultation_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $v->doctor?->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">{{ $v->status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $v->payment_status }} · {{ $v->payment_method ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a target="_blank" href="{{ route('counter.opd.print', ['id' => $v->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="6" message="No OP visits." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>

            @if($datasets['visits'])
                <div class="mt-4">
                    {{ $datasets['visits']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'appointments')
        <x-card title="Appointment Schedule">
            <x-slot:action>
                <button wire:click="export('visits')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('visits')">Export CSV</span>
                    <span wire:loading wire:target="export('visits')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Date</x-table.th>
                            <x-table.th>Token</x-table.th>
                            <x-table.th>Doctor</x-table.th>
                            <x-table.th>Status</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['appointments'] as $a)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $a->consultation_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm font-black text-indigo-600 dark:text-indigo-400">#{{ $a->token_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $a->doctor?->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">{{ $a->status }}</td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="4" message="No appointments scheduled." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['appointments'] as $a)
                    <div class="p-4">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $a->consultation_date?->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Token #{{ $a->token_number }} · {{ $a->doctor?->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $a->status }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No appointments scheduled.</div>
                @endforelse
            </div>
            @if($datasets['appointments'])
                <div class="mt-4">
                    {{ $datasets['appointments']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'admissions')
        <x-card title="Inpatient Admissions">
            <x-slot:action>
                <button wire:click="export('admissions')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('admissions')">Export CSV</span>
                    <span wire:loading wire:target="export('admissions')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Admission No</x-table.th>
                            <x-table.th>Admitted</x-table.th>
                            <x-table.th>Ward/Bed</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['admissions'] as $a)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white">{{ $a->admission_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $a->admission_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $a->bed?->ward?->name ?? '—' }} · {{ $a->bed?->bed_number ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">{{ $a->status }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if($a->discharge_date)
                                        <a href="{{ route('discharge.summary', ['admission' => $a->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Summary</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No admissions found." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['admissions'] as $a)
                    <div class="p-4">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $a->admission_number }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $a->admission_date?->format('d M Y') }} · {{ $a->status }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $a->bed?->ward?->name ?? '—' }} · {{ $a->bed?->bed_number ?? '—' }}</p>
                        @if($a->discharge_date)
                            <div class="mt-3">
                                <a href="{{ route('discharge.summary', ['admission' => $a->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Summary</a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No admissions found.</div>
                @endforelse
            </div>
            @if($datasets['admissions'])
                <div class="mt-4">
                    {{ $datasets['admissions']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'discharges')
        <x-card title="Discharge Summaries">
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Admission No</x-table.th>
                            <x-table.th>Discharged</x-table.th>
                            <x-table.th>Ward/Bed</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['discharges'] as $a)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white">{{ $a->admission_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $a->discharge_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $a->bed?->ward?->name ?? '—' }} · {{ $a->bed?->bed_number ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('discharge.summary', ['admission' => $a->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">View</a>
                                    <a target="_blank" href="{{ route('discharge.print', ['admission' => $a->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="4" message="No discharge summaries found." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['discharges'] as $a)
                    <div class="p-4">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $a->admission_number }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $a->discharge_date?->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $a->bed?->ward?->name ?? '—' }} · {{ $a->bed?->bed_number ?? '—' }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('discharge.summary', ['admission' => $a->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">View</a>
                            <a target="_blank" href="{{ route('discharge.print', ['admission' => $a->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No discharge summaries found.</div>
                @endforelse
            </div>
            @if($datasets['discharges'])
                <div class="mt-4">
                    {{ $datasets['discharges']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'prescriptions')
        <x-card title="Medication Records">
            <x-slot:action>
                <button wire:click="export('prescriptions')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('prescriptions')">Export CSV</span>
                    <span wire:loading wire:target="export('prescriptions')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Date</x-table.th>
                            <x-table.th>Doctor</x-table.th>
                            <x-table.th>Diagnosis</x-table.th>
                            <x-table.th>Follow Up</x-table.th>
                            <x-table.th class="text-right">Actions</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['prescriptions'] as $p)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $p->created_at?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $p->doctor?->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $p->diagnosis ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $p->follow_up_date ? \Illuminate\Support\Carbon::parse($p->follow_up_date)->format('d M Y') : '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a target="_blank" href="{{ route('counter.prescriptions.print', ['id' => $p->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No medication records." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['prescriptions'] as $p)
                    <div class="p-4">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $p->created_at?->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $p->doctor?->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $p->diagnosis ?: '—' }}</p>
                        <div class="mt-3">
                            <a target="_blank" href="{{ route('counter.prescriptions.print', ['id' => $p->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No medication records.</div>
                @endforelse
            </div>
            @if($datasets['prescriptions'])
                <div class="mt-4">
                    {{ $datasets['prescriptions']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'labs')
        <x-card title="Diagnostic Reports">
            <x-slot:action>
                <button wire:click="export('labs')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('labs')">Export CSV</span>
                    <span wire:loading wire:target="export('labs')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Date</x-table.th>
                            <x-table.th>Test</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th>Collected</x-table.th>
                            <x-table.th>Completed</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['labs'] as $o)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $o->created_at?->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $o->labTest?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">{{ $o->status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $o->collected_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $o->completed_at?->format('d M Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="5" message="No diagnostic reports." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['labs'] as $o)
                    <div class="p-4">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $o->labTest?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $o->created_at?->format('d M Y') }} · {{ $o->status }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Collected: {{ $o->collected_at?->format('d M Y H:i') ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Completed: {{ $o->completed_at?->format('d M Y H:i') ?? '—' }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No diagnostic reports.</div>
                @endforelse
            </div>
            @if($datasets['labs'])
                <div class="mt-4">
                    {{ $datasets['labs']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'payments')
        <x-card title="Payment History">
            <x-slot:action>
                <button wire:click="export('payments')" wire:loading.attr="disabled" class="btn btn-secondary">
                    <span wire:loading.remove wire:target="export('payments')">Export CSV</span>
                    <span wire:loading wire:target="export('payments')">Exporting...</span>
                </button>
            </x-slot:action>
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Date</x-table.th>
                            <x-table.th>Bill No</x-table.th>
                            <x-table.th class="text-right">Amount</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th>Method</x-table.th>
                            <x-table.th class="text-right">Receipt</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['payments'] as $b)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $b->created_at?->format('d M Y') }}</td>
                                <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $b->bill_number }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white text-right">₹{{ number_format($b->total_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">{{ $b->payment_status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $b->payment_method ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a target="_blank" href="{{ route('billing.bills.print', ['bill' => $b->id]) }}" class="btn btn-ghost px-3 py-2 text-xs">Print</a>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="6" message="No payment records." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['payments'] as $b)
                    <div class="p-4">
                        <p class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $b->bill_number }}</p>
                        <p class="text-sm font-black text-gray-900 dark:text-white mt-1">₹{{ number_format($b->total_amount, 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $b->created_at?->format('d M Y') }} · {{ $b->payment_status }}</p>
                        <div class="mt-3">
                            <a target="_blank" href="{{ route('billing.bills.print', ['bill' => $b->id]) }}" class="btn btn-secondary px-3 py-2 text-xs">Print</a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No payment records.</div>
                @endforelse
            </div>
            @if($datasets['payments'])
                <div class="mt-4">
                    {{ $datasets['payments']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'vitals')
        <x-card title="Vital Logs">
            <div class="hidden md:block">
                <x-table.wrapper>
                    <thead>
                        <tr>
                            <x-table.th>Date</x-table.th>
                            <x-table.th class="text-right">Temp</x-table.th>
                            <x-table.th class="text-right">Weight</x-table.th>
                            <x-table.th class="text-right">BP</x-table.th>
                            <x-table.th class="text-right">SPO2</x-table.th>
                            <x-table.th>Recorded By</x-table.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets['vitals'] as $v)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $v->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 text-right">{{ $v->temperature ? $v->temperature . '°F' : '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 text-right">{{ $v->weight ? $v->weight . ' kg' : '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 text-right">
                                    {{ $v->bp_systolic && $v->bp_diastolic ? ($v->bp_systolic . '/' . $v->bp_diastolic) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 text-right">{{ $v->spo2 ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $v->recorder?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-table.empty colSpan="6" message="No vital logs." />
                        @endforelse
                    </tbody>
                </x-table.wrapper>
            </div>
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($datasets['vitals'] as $v)
                    <div class="p-4">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $v->created_at?->format('d M Y H:i') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Temp: {{ $v->temperature ? $v->temperature . '°F' : '—' }} · Wt: {{ $v->weight ? $v->weight . 'kg' : '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">BP: {{ $v->bp_systolic && $v->bp_diastolic ? ($v->bp_systolic . '/' . $v->bp_diastolic) : '—' }} · SPO2: {{ $v->spo2 ?? '—' }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">No vital logs.</div>
                @endforelse
            </div>
            @if($datasets['vitals'])
                <div class="mt-4">
                    {{ $datasets['vitals']->links() }}
                </div>
            @endif
        </x-card>
    @endif

    @if($tab === 'vaccinations')
        <x-card title="Vaccination Records" :noPad="true">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800/50 text-[11px] font-black text-gray-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Recommended Age</th>
                            <th class="px-6 py-4">Vaccine</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-900/30">
                        @foreach($allVaccines as $v)
                            @php $given = $vaccinations->firstWhere('vaccine_id', $v->id); @endphp
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-800 dark:text-gray-100">{{ $v->recommended_age }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white">{{ $v->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($given)
                                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase">Given</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-400 text-[10px] font-black uppercase">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(!$given)
                                        <button wire:click="recordVaccination({{ $v->id }}, '{{ date('Y-m-d') }}')" class="btn btn-primary px-3 py-2 text-xs">Record</button>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $given->date_given ? \Illuminate\Support\Carbon::parse($given->date_given)->format('d M Y') : '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>
