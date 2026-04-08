@extends('layouts.app')

@section('title', 'Discharge Summary')

@section('content')
<x-page-header title="Discharge Summary" subtitle="Review discharge details and print the summary." :back="route('discharge.index')" backLabel="Discharge">
    <x-slot name="actions">
        @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id') && $admission->finalBill)
            <a class="btn btn-secondary" target="_blank" href="{{ route('billing.bills.print', ['bill' => $admission->finalBill->id]) }}">Final Bill</a>
        @endif
        <a class="btn btn-primary" target="_blank" href="{{ route('discharge.print', $admission->id) }}">Print</a>
    </x-slot>
</x-page-header>

<div class="glass-card p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Patient</h3>
            <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p class="font-bold">{{ $admission->patient->full_name }}</p>
                <p>UHID: <span class="font-semibold">{{ $admission->patient->uhid }}</span></p>
                <p>Phone: <span class="font-semibold">{{ $admission->patient->phone }}</span></p>
            </div>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Admission</h3>
            <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p>Admission No: <span class="font-semibold">{{ $admission->admission_number }}</span></p>
                <p>Ward/Bed: <span class="font-semibold">{{ $admission->bed?->ward?->name ?? 'N/A' }} / {{ $admission->bed?->bed_number ?? 'N/A' }}</span></p>
                <p>Doctor: <span class="font-semibold">{{ $admission->doctor?->full_name ?? 'Unassigned' }}</span></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Dates</h3>
            <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p>Admitted: <span class="font-semibold">{{ $admission->admission_date?->format('d M Y, H:i') ?? '—' }}</span></p>
                <p>Discharged: <span class="font-semibold">{{ $admission->discharge_date?->format('d M Y, H:i') ?? '—' }}</span></p>
                <p>Status: <span class="font-semibold">{{ $admission->status }}</span></p>
            </div>
        </div>
        <div class="glass-card p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Reason</h3>
            <p class="text-sm text-slate-700 dark:text-slate-200">
                {{ $admission->reason_for_admission ?: '—' }}
            </p>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Discharge Notes</h3>
        <p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">
            {{ $admission->notes ?: '—' }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-card p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Vitals</h3>
            @php $latestVital = $admission->vitals?->sortByDesc('created_at')->first(); @endphp
            @if($latestVital)
                <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                    <p>Date: <span class="font-semibold">{{ $latestVital->created_at?->format('d M Y, H:i') }}</span></p>
                    <p>Temp: <span class="font-semibold">{{ $latestVital->temperature ?? '—' }}</span></p>
                    <p>Weight: <span class="font-semibold">{{ $latestVital->weight ?? '—' }}</span></p>
                    <p>BP: <span class="font-semibold">{{ $latestVital->bp_systolic ?? '—' }}{{ $latestVital->bp_diastolic ? '/' . $latestVital->bp_diastolic : '' }}</span></p>
                    <p>SpO2: <span class="font-semibold">{{ $latestVital->spo2 ?? '—' }}</span></p>
                </div>
            @else
                <p class="text-sm text-slate-600 dark:text-slate-300">No vitals recorded.</p>
            @endif
        </div>

        <div class="glass-card p-5 lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Lab Orders</h3>
            @if($admission->labOrders?->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-slate-400">
                                <th class="py-2">Test</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Collected</th>
                                <th class="py-2">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($admission->labOrders as $o)
                                <tr>
                                    <td class="py-2 font-semibold">{{ $o->labTest?->name ?? '—' }}</td>
                                    <td class="py-2">{{ $o->status }}</td>
                                    <td class="py-2">{{ $o->collected_at ? \Illuminate\Support\Carbon::parse($o->collected_at)->format('d M Y, H:i') : '—' }}</td>
                                    <td class="py-2">{{ $o->completed_at ? \Illuminate\Support\Carbon::parse($o->completed_at)->format('d M Y, H:i') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-600 dark:text-slate-300">No lab orders.</p>
            @endif
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Medications</h3>
        @php
            $dispensed = $admission->medications?->filter(fn ($m) => (bool) $m->is_dispensed) ?? collect();
        @endphp
        @if($dispensed->count())
            <div class="space-y-2">
                @foreach($dispensed as $rx)
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Dispensed: {{ $rx->dispensed_at?->format('d M Y, H:i') ?? '—' }}</p>
                        @php $meds = is_array($rx->medicines) ? $rx->medicines : []; @endphp
                        @if(count($meds))
                            <ul class="mt-2 text-sm text-slate-700 dark:text-slate-200 list-disc pl-5 space-y-1">
                                @foreach($meds as $m)
                                    <li>
                                        <span class="font-semibold">{{ $m['name'] ?? 'Medicine' }}</span>
                                        @if(isset($m['qty'])) · Qty {{ $m['qty'] }} @endif
                                        @if(isset($m['dose']) && $m['dose']) · {{ $m['dose'] }} @endif
                                        @if(isset($m['frequency']) && $m['frequency']) · {{ $m['frequency'] }} @endif
                                        @if(isset($m['duration']) && $m['duration']) · {{ $m['duration'] }} @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-2">No medicines listed.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-600 dark:text-slate-300">No dispensed medications.</p>
        @endif
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Final Bill</h3>
        @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id') && $admission->finalBill)
            <div class="flex items-center justify-between gap-4">
                <div class="text-sm text-slate-700 dark:text-slate-200">
                    <p>Bill No: <span class="font-semibold">{{ $admission->finalBill->bill_number }}</span></p>
                    <p>Status: <span class="font-semibold">{{ $admission->finalBill->payment_status }}</span></p>
                    <p>Paid: <span class="font-semibold">₹{{ number_format((float) $admission->finalBill->paid_amount, 2) }}</span></p>
                    <p>Due: <span class="font-semibold">₹{{ number_format(max(0, (float) $admission->finalBill->balance_amount), 2) }}</span></p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">₹{{ number_format($admission->finalBill->total_amount, 2) }}</p>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-slate-600 dark:text-slate-300">Final bill not generated.</p>
                @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id'))
                    <form method="POST" action="{{ route('discharge.final-bill', ['admission' => $admission->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Generate Final Bill</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
