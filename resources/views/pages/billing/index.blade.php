@extends('layouts.app')

@section('title', 'Billing Management')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Billing Management</h1>
        <p class="section-subtitle">Consolidate charges, apply discounts, and complete settlement.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-secondary">Download Report</button>
        <button class="btn btn-primary">Create Bill</button>
    </div>
</div>

<livewire:counter.billing-list />
@endsection
