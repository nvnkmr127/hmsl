@extends('layouts.app')

@section('title', 'Pharmacy Management')

@section('content')
<x-breadcrumb />

<x-page-header title="Pharmacy Management" subtitle="Track prescriptions, dispense medicines, and manage pharmacy stock.">
    <x-slot name="actions">
        <button class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Export Stock
        </button>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            New Dispense
        </button>
    </x-slot>
</x-page-header>

<livewire:pharmacy.pharmacy-orders />
@endsection
