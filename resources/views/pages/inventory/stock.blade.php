@extends('layouts.app')

@section('title', 'Inventory Stock')

@section('content')
<x-page-header title="Inventory Stock" subtitle="View item-wise stock levels and reorder alerts." :back="route('inventory.index')" backLabel="Inventory">
    <x-slot name="actions">
        <button class="btn btn-primary">Record Movement</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Current Stock</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No stock lines available yet. Item-level balances will render here once transactions are added.</p>
</div>
@endsection
