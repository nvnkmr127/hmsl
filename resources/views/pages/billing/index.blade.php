@extends('layouts.app')

@section('title', 'Billing Management')

@section('content')
<x-breadcrumb />

<x-page-header title="Billing Management" subtitle="Consolidate charges, apply discounts, and complete settlement.">
    <x-slot name="actions">
        @can('view reports')
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reports</a>
        @endcan
        <button class="btn btn-primary" @click="$dispatch('open-modal', { name: 'billing-create-modal' })">Create Bill</button>
    </x-slot>
</x-page-header>

<x-counter-nav />

<livewire:counter.billing-list />
<livewire:counter.bill-generate />

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('bill-generated', (event) => {
            const billId = event?.billId ?? event?.[0]?.billId ?? event?.[0]?.bill_id ?? event?.[0];
            if (!billId) return;
            const url = "{{ route('billing.bills.print', ['bill' => ':id']) }}".replace(':id', billId);
            window.open(url, '_blank');
        });
    });
</script>

<x-modal name="billing-create-modal" title="Create Bill">
    <div class="space-y-4">
        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
            <p class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Choose method</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">Create bill from an OP token or directly for a patient.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button class="btn btn-secondary" @click="$dispatch('open-modal', { name: 'billing-find-token-modal' })">
                From OP Token
            </button>
            <button class="btn btn-secondary" @click="$dispatch('open-modal', { name: 'billing-find-patient-modal' })">
                From Patient
            </button>
        </div>
    </div>
</x-modal>

<x-modal name="billing-find-token-modal" title="Find OP Token">
    <livewire:counter.billing-find-token />
</x-modal>

<x-modal name="billing-find-patient-modal" title="Select Patient">
    <livewire:counter.billing-find-patient />
</x-modal>
@endsection
