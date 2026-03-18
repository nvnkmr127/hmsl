@extends('layouts.app')

@section('title', 'Pharmacy Stock')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Pharmacy Stock</h1>
        <p class="section-subtitle">Monitor medicine inventory and refill thresholds.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('pharmacy.index') }}" class="btn btn-secondary">Back to Pharmacy</a>
        <button class="btn btn-primary">Adjust Stock</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Stock Snapshot</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No stock movements recorded yet. Add stock transactions to display real-time availability.</p>
</div>
@endsection
