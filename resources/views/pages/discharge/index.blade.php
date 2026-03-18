@extends('layouts.app')

@section('title', 'Discharge Management')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Discharge Management</h1>
        <p class="section-subtitle">Prepare patient discharge summaries, clearance checks, and final instructions.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-secondary">Export Summary</button>
        <button class="btn btn-primary">New Discharge</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Pending Discharges</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No pending discharge workflows yet. Linked admissions will appear here when doctors mark patients ready for discharge.</p>
</div>
@endsection
