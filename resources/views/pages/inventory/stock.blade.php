@extends('layouts.app')

@section('title', 'Inventory Stock')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Inventory Stock</h1>
        <p class="section-subtitle">View item-wise stock levels and reorder alerts.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Back to Inventory</a>
        <button class="btn btn-primary">Record Movement</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Current Stock</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No stock lines available yet. Item-level balances will render here once transactions are added.</p>
</div>
@endsection
