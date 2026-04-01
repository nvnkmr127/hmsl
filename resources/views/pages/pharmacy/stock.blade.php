@extends('layouts.app')

@section('title', 'Pharmacy Stock')

@section('content')
<x-page-header title="Pharmacy Stock" subtitle="Monitor medicine inventory and refill thresholds." :back="route('pharmacy.index')" backLabel="Pharmacy">
    <x-slot name="actions">
        <button class="btn btn-primary">Adjust Stock</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Stock Snapshot</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No stock movements recorded yet. Add stock transactions to display real-time availability.</p>
</div>
@endsection
