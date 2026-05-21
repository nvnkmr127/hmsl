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

<div class="max-w-6xl mx-auto space-y-8">
    
    <!-- 1. Patient & Admission Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
        <div class="absolute top-0 right-0 p-6 opacity-10 pointer-events-none">
            <svg class="w-32 h-32 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            <!-- Patient Info -->
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold uppercase tracking-wider mb-2">
                    Patient Details
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ $admission->patient->full_name }}</h2>
                    <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                        <p class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-1"><span>UHID:</span> <span class="font-bold">{{ $admission->patient->uhid }}</span></p>
                        <p class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-1"><span>Age/Gender:</span> <span class="font-bold">{{ $admission->patient->age ?? '-' }} / {{ $admission->patient->gender ?? '-' }}</span></p>
                        <p class="flex justify-between pb-1"><span>Contact:</span> <span class="font-bold">{{ $admission->patient->phone ?? 'N/A' }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Admission Info -->
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
                    Admission Info
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">#{{ $admission->admission_number }}</h2>
                    <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
                        <p class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-1"><span>Ward/Bed:</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $admission->bed?->ward?->name ?? 'N/A' }} / {{ $admission->bed?->bed_number ?? 'N/A' }}</span></p>
                        <p class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-1"><span>Primary Doctor:</span> <span class="font-bold text-slate-800 dark:text-slate-200">Dr. {{ $admission->doctor?->full_name ?? 'Unassigned' }}</span></p>
                        <p class="flex justify-between pb-1"><span>Status:</span> <span class="font-bold"><x-badge :type="$admission->status === 'Discharged' ? 'success' : 'warning'">{{ $admission->status }}</x-badge></span></p>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">
                    Timeline
                </div>
                <div class="mt-2 space-y-4">
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <div class="w-0.5 h-full bg-blue-200 dark:bg-blue-900/50 my-1"></div>
                        </div>
                        <div class="pb-2">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Admitted</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $admission->admission_date?->format('d M Y, h:i A') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full {{ $admission->discharge_date ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Discharged</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $admission->discharge_date?->format('d M Y, h:i A') ?? 'Pending' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Clinical Summary (Dynamic based on DischargeSummary model) -->
    @if($admission->dischargeSummary)
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Clinical Discharge Summary
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Final Diagnosis</h4>
                        <p class="text-sm text-slate-800 dark:text-slate-200 font-medium bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                            {{ $admission->dischargeSummary->final_diagnosis ?: 'Not documented' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Admission Diagnosis</h4>
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            {{ $admission->dischargeSummary->admission_diagnosis ?: $admission->reason_for_admission ?: '—' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Condition at Discharge</h4>
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            {{ $admission->dischargeSummary->condition_at_discharge ?: '—' }}
                        </p>
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Treatment Summary</h4>
                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                            {{ $admission->dischargeSummary->treatment_summary ?: '—' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Procedures / Operations</h4>
                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">
                            {{ $admission->dischargeSummary->procedures_done ?: 'None documented' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Investigations Summary</h4>
                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">
                            {{ $admission->dischargeSummary->investigations_summary ?: '—' }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-100 dark:border-slate-700 bg-slate-50/30 dark:bg-slate-800/30 p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Advice & Follow Up</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if($admission->dischargeSummary->diet_advice)
                    <div>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 block mb-1">Diet Advice</span>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $admission->dischargeSummary->diet_advice }}</p>
                    </div>
                    @endif
                    @if($admission->dischargeSummary->activity_advice)
                    <div>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 block mb-1">Activity Advice</span>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $admission->dischargeSummary->activity_advice }}</p>
                    </div>
                    @endif
                    @if($admission->dischargeSummary->follow_up_date)
                    <div>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 block mb-1">Next Follow Up</span>
                        <p class="text-sm text-indigo-600 font-semibold">{{ $admission->dischargeSummary->follow_up_date->format('d M Y') }}</p>
                        @if($admission->dischargeSummary->follow_up_notes)
                            <p class="text-xs text-slate-500 mt-1">{{ $admission->dischargeSummary->follow_up_notes }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- Fallback if no formal discharge summary exists -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-lg font-black text-slate-800 dark:text-white">Admission Notes & Reason</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Reason for Admission</h4>
                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $admission->reason_for_admission ?: '—' }}</p>
                </div>
                <div>
                    <livewire:ipd.edit-discharge-note :admission="$admission" />
                </div>
            </div>
        </div>
    @endif

    <!-- 3. Medications -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                Discharge Medications
            </h3>
            <livewire:ipd.manage-discharge-medications :admission="$admission" />
        </div>
        <div class="p-6">
            @php
                $meds = $admission->dischargeSummary ? $admission->dischargeSummary->medications : collect();
            @endphp
            
            @if($meds->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($meds as $rx)
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700">
                            <h4 class="font-bold text-slate-800 dark:text-slate-200">{{ $rx->medicine_name }}</h4>
                            <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-400">
                                @if($rx->dosage)<p><span class="font-semibold text-slate-700 dark:text-slate-300">Dosage:</span> {{ $rx->dosage }}</p>@endif
                                @if($rx->frequency)<p><span class="font-semibold text-slate-700 dark:text-slate-300">Freq:</span> {{ $rx->frequency }}</p>@endif
                                @if($rx->duration)<p><span class="font-semibold text-slate-700 dark:text-slate-300">Duration:</span> {{ $rx->duration }}</p>@endif
                                @if($rx->route)<p><span class="font-semibold text-slate-700 dark:text-slate-300">Route:</span> {{ $rx->route }}</p>@endif
                                @if($rx->instructions)<p class="pt-2 text-xs text-indigo-600 dark:text-indigo-400 font-medium">Note: {{ $rx->instructions }}</p>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 italic">No discharge medications prescribed.</p>
            @endif
        </div>
    </div>

    <!-- 4. Vitals & Labs (Side by side) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Vitals -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-black text-slate-800 dark:text-white uppercase tracking-wider text-sm">Last Recorded Vitals</h3>
            </div>
            <div class="p-6">
                @php $latestVital = $admission->ipdVitals?->sortByDesc('recorded_at')->first(); @endphp
                @if($latestVital)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-3 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
                            <p class="text-[10px] uppercase font-bold text-rose-500 mb-1">Temp</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $latestVital->temperature ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <p class="text-[10px] uppercase font-bold text-blue-500 mb-1">BP</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $latestVital->bp_systolic ?? '-' }}/{{ $latestVital->bp_diastolic ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                            <p class="text-[10px] uppercase font-bold text-emerald-500 mb-1">SpO2</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $latestVital->spo2 ?? '-' }}%</p>
                        </div>
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                            <p class="text-[10px] uppercase font-bold text-purple-500 mb-1">Weight</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $latestVital->weight ?? '-' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-500 italic">No vitals recorded.</p>
                @endif
            </div>
        </div>

        <!-- Labs -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-black text-slate-800 dark:text-white uppercase tracking-wider text-sm">Lab Orders Summary</h3>
            </div>
            <div class="p-6">
                @if($admission->labOrders?->count())
                    <div class="space-y-3">
                        @foreach($admission->labOrders->take(3) as $o)
                            <div class="flex items-center justify-between p-3 border border-slate-100 dark:border-slate-700 rounded-lg">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $o->labTest?->name ?? 'Unknown Test' }}</span>
                                <x-badge :type="match($o->status){'Completed'=>'success', 'Pending'=>'warning', default=>'secondary'}">{{ $o->status }}</x-badge>
                            </div>
                        @endforeach
                        @if($admission->labOrders->count() > 3)
                            <p class="text-xs text-center text-slate-500 mt-2">+ {{ $admission->labOrders->count() - 3 }} more orders</p>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-slate-500 italic">No lab orders.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- 5. Final Bill Section (Kept from previous update) -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                Final Bill Summary
            </h3>
            @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id') && $admission->finalBill)
                <button x-data @click="$dispatch('open-modal', { name: 'discharge-process-modal' })" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    Edit Bill
                </button>
            @endif
        </div>

        <div class="p-6">
            @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id') && $admission->finalBill)
                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-lg w-12 text-center">#</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 text-center">Category</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Rate</th>
                                <th class="px-4 py-3 text-right rounded-tr-lg">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($admission->finalBill->items as $index => $item)
                                @php
                                    $rawName = $item->item_name ?? $item->name;
                                    $dateStr = $item->created_at?->format('d/m/Y') ?? '—';
                                    
                                    // Extract embedded dates like [12/05 - 15/05]
                                    if (preg_match('/\[(.*?)\]/', $rawName, $matches)) {
                                        $dateStr = trim($matches[1]);
                                        $rawName = trim(str_replace($matches[0], '', $rawName));
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="px-4 py-3 text-slate-400 font-medium text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs font-medium">{{ $dateStr }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $rawName }}</td>
                                    <td class="px-4 py-3 text-center"><x-badge type="secondary">{{ $item->item_type ?? $item->type }}</x-badge></td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300 font-medium">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500 dark:text-slate-400">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">₹{{ number_format($item->total_price ?? ($item->quantity * $item->unit_price), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900/40 p-5 rounded-xl border border-slate-100 dark:border-slate-800">
                    <div class="text-sm text-slate-700 dark:text-slate-200 w-full sm:w-auto">
                        <p class="mb-1">Bill No: <span class="font-bold text-slate-900 dark:text-white">{{ $admission->finalBill->bill_number }}</span></p>
                        <p class="mb-1">Status: <span class="font-bold {{ $admission->finalBill->payment_status === 'Paid' ? 'text-green-500' : 'text-amber-500' }}">{{ $admission->finalBill->payment_status }}</span></p>
                        <p class="mb-1">Paid: <span class="font-semibold text-green-600">₹{{ number_format((float) $admission->finalBill->paid_amount, 2) }}</span></p>
                        <p>Due: <span class="font-semibold text-red-500">₹{{ number_format(max(0, (float) $admission->finalBill->balance_amount), 2) }}</span></p>
                    </div>
                    <div class="text-right w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-slate-700 pt-4 sm:pt-0 sm:pl-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Bill Amount</p>
                        <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400">₹{{ number_format($admission->finalBill->total_amount, 2) }}</p>
                    </div>
                </div>

                <livewire:ipd.discharge-process :admission="$admission" :hideTrigger="true" />
            @else
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <div class="w-16 h-16 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Final Bill Not Generated</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 max-w-md">The final bill for this admission has not been processed yet. Generate it now to complete the discharge process.</p>
                    
                    @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id'))
                        <form method="POST" action="{{ route('discharge.final-bill', ['admission' => $admission->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary px-6">Generate Final Bill</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
