@extends('layouts.app')

@section('title', 'Inventory Suppliers')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Inventory Suppliers</h1>
        <p class="section-subtitle">Manage supplier records, contacts, and procurement terms.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Back to Inventory</a>
        <button class="btn btn-primary">Add Supplier</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Supplier Directory</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No suppliers found. Add vendor profiles here to support purchase workflows.</p>
</div>
@endsection
