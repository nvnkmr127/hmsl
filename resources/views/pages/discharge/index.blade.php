@extends('layouts.app')

@section('title', 'Discharge Management')

@section('content')
<x-page-header title="Discharge Management" subtitle="Prepare patient discharge summaries, clearance checks, and final instructions.">
    <x-slot name="actions">
        <button class="btn btn-secondary">Export Summary</button>
        <button class="btn btn-primary">New Discharge</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Pending Discharges</h3>
    <livewire:discharge.discharge-management />
</div>
@endsection
