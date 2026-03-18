@extends('layouts.app')

@section('title', 'Pharmacy Orders')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Pharmacy Orders</h1>
        <p class="section-subtitle">Review incoming medicine requests and track fulfillment status.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('pharmacy.index') }}" class="btn btn-secondary">Back to Pharmacy</a>
        <button class="btn btn-primary">Create Order</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Order Queue</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No active orders yet. Connect this page to your order workflow to start processing requests.</p>
</div>
@endsection
