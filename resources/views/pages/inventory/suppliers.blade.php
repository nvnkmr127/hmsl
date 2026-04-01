@extends('layouts.app')

@section('title', 'Inventory Suppliers')

@section('content')
<x-page-header title="Inventory Suppliers" subtitle="Manage supplier records, contacts, and procurement terms." :back="route('inventory.index')" backLabel="Inventory">
    <x-slot name="actions">
        <button class="btn btn-primary">Add Supplier</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Supplier Directory</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No suppliers found. Add vendor profiles here to support purchase processes.</p>
</div>
@endsection
