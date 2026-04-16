@extends('layouts.app')

@section('title', 'Discharge Management')

@section('content')
<x-breadcrumb />

<x-page-header title="Discharge Management" subtitle="Prepare patient discharge summaries, clearance checks, and final instructions.">
    <x-slot name="actions">
        <a href="{{ route('discharge.export') }}" class="btn btn-secondary">Export Summary</a>
        <a href="#pending-discharges" class="btn btn-primary">New Discharge</a>
    </x-slot>
</x-page-header>

<div id="pending-discharges" class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Pending Discharges</h3>
    <livewire:discharge.discharge-management />
</div>
@endsection
