@extends('layouts.app')

@section('title', 'IPD Patient - ' . $admission->patient->full_name)

@section('content')
    {{-- Auto-open billing popup if ?openBilling=1 --}}
    <div x-data x-init="
        const params = new URLSearchParams(window.location.search);
        if (params.get('openBilling') === '1') {
            $nextTick(() => $dispatch('open-modal', { name: 'discharge-process-modal' }));
        }
    ">
    <x-page-header :title="$admission->patient->full_name" :subtitle="'Admission: ' . $admission->admission_number">
        <x-slot name="actions">
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary">
                Back to Admissions
            </a>
            @if($admission->status === 'Admitted')
                <a href="{{ route('counter.ipd.discharge', $admission->id) }}" class="btn btn-primary">
                    Discharge Summary
                </a>
            @else
                <a href="{{ route('discharge.summary', $admission->id) }}" class="btn btn-primary">
                    View Summary
                </a>
            @endif
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <x-card title="Patient Information">
                <x-patient-identity :patient="$admission->patient" :subtitle="$admission->uhid" />
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Age:</span>
                        <span class="font-semibold">{{ $admission->patient->age }} {{ $admission->patient->age_unit ?? 'years' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gender:</span>
                        <span class="font-semibold">{{ $admission->patient->gender }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Phone:</span>
                        <span class="font-semibold">{{ $admission->patient->phone }}</span>
                    </div>
                </div>
            </x-card>

            <x-card title="Admission Details" class="mt-4">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ward:</span>
                        <span class="font-semibold">{{ $admission->bed?->ward?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bed:</span>
                        <span class="font-semibold">{{ $admission->bed?->bed_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Doctor:</span>
                        <span class="font-semibold">{{ $admission->doctor?->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Admission:</span>
                        <span class="font-semibold">{{ $admission->admission_date->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Days Admitted:</span>
                        <span class="font-semibold">{{ $admission->days_admitted }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <x-badge :type="$admission->status === 'Admitted' ? 'success' : ($admission->status === 'Discharged' ? 'info' : 'secondary')">{{ $admission->status }}</x-badge>
                    </div>
                </div>

                @if($admission->guardian_name)
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Guardian Info</h4>
                        <div class="space-y-1 text-sm">
                            <p><span class="text-gray-500">Name:</span> {{ $admission->guardian_name }}</p>
                            <p><span class="text-gray-500">Relation:</span> {{ $admission->guardian_relation }}</p>
                            <p><span class="text-gray-500">Phone:</span> {{ $admission->guardian_phone }}</p>
                        </div>
                    </div>
                @endif
            </x-card>

            @if($admission->reason_for_admission)
                <x-card title="Reason for Admission" class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $admission->reason_for_admission }}</p>
                </x-card>
            @endif

            @if($admission->status === 'Admitted')
                <x-card title="Quick Actions" class="mt-4">
                    <div class="space-y-2">
                        <livewire:ipd.discharge-process :admission="$admission" />
                        <div class="flex gap-2">
                            <livewire:ipd.transfer-bed :admission="$admission" />
                            @if($admission->finalBill)
                                <a href="{{ route('billing.bills.print', $admission->finalBill->id) }}" target="_blank" class="btn btn-secondary w-full text-xs">
                                    Print Bill
                                </a>
                            @endif
                        </div>
                    </div>
                </x-card>
            @else
                {{-- Embed discharge-process (hidden trigger) so modal is available for discharged patients too --}}
                <livewire:ipd.discharge-process :admission="$admission" :hideTrigger="true" />
            @endif
        </div>

        <div class="lg:col-span-3">
            <div class="border-b border-gray-100 dark:border-gray-800 mb-6">
                <nav class="flex gap-1 -mb-px">
                    <a href="#vitals" class="px-4 py-3 text-sm font-bold border-b-2 border-indigo-500 text-indigo-600">
                        Vitals
                    </a>
                    <a href="#medications" class="px-4 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Medications
                    </a>
                    <a href="#notes" class="px-4 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Clinical Notes
                    </a>
                    <a href="#lab" class="px-4 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Lab Orders
                    </a>
                    <a href="#billing" class="px-4 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Billing
                    </a>
                </nav>
            </div>

            <div id="vitals">
                <livewire:ipd.ipd-vitals :admission="$admission" />
            </div>

            <div id="medications" class="mt-8">
                <livewire:ipd.medication-chart :admission="$admission" />
            </div>

            <div id="notes" class="mt-8">
                <livewire:ipd.ipd-notes :admission="$admission" />
            </div>

            <div id="lab" class="mt-8">
                <livewire:ipd.ipd-lab-orders :admission="$admission" />
            </div>

            <div id="billing" class="mt-8">
                <x-card title="Billing">
                    @if($admission->finalBill)
                        <div class="p-4 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="font-bold text-indigo-900 dark:text-indigo-200">Bill #{{ $admission->finalBill->bill_number }}</p>
                                    <p class="text-sm text-indigo-700 dark:text-indigo-300">
                                        Total: ₹{{ number_format($admission->finalBill->total_amount, 2) }}
                                    </p>
                                </div>
                                <x-badge :type="$admission->finalBill->payment_status === 'Paid' ? 'success' : ($admission->finalBill->payment_status === 'Partially Paid' ? 'warning' : 'danger')">
                                    {{ $admission->finalBill->payment_status }}
                                </x-badge>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm text-indigo-800 dark:text-indigo-200">
                                    Paid: ₹{{ number_format($admission->finalBill->paid_amount, 2) }}
                                </p>
                                @if($admission->finalBill->balance_amount > 0)
                                    <span class="text-indigo-400">|</span>
                                    <p class="text-sm text-indigo-800 dark:text-indigo-200">
                                        Due: ₹{{ number_format($admission->finalBill->balance_amount, 2) }}
                                    </p>
                                @endif
                            </div>
                            
                            @if($admission->finalBill->items->count() > 0)
                                <div class="mb-4 space-y-2 max-h-48 overflow-y-auto pr-2">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bill Breakdown</p>
                                    @foreach($admission->finalBill->items as $item)
                                        <div class="flex justify-between text-xs border-b border-indigo-100 dark:border-indigo-900/50 pb-1">
                                            <span class="text-indigo-900 dark:text-indigo-200">{{ $item->item_name }}</span>
                                            <span class="font-bold text-indigo-900 dark:text-indigo-200">₹{{ number_format($item->total_price, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ route('billing.bills.print', $admission->finalBill->id) }}" target="_blank" class="btn btn-primary mt-4 text-xs">
                                Print Bill
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">No final bill generated yet.</p>
                            @if($admission->status === 'Discharged')
                                <form action="{{ route('discharge.final-bill', $admission->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Generate Final Bill</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    </div>

    @unless($admission->status === 'Discharged')
        <livewire:doctor.lab-order-composer />
    @endunless
    </div>{{-- end x-data auto-open wrapper --}}
@endsection
