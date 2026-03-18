@extends('layouts.app')

@section('title', 'Laboratory Tests')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Laboratory Tests</h1>
        <p class="section-subtitle">Manage test catalog, pricing, and sample requirements.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('laboratory.index') }}" class="btn btn-secondary">Back to Laboratory</a>
        <button class="btn btn-primary">Add Test</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Test Catalog</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No test definitions available yet. Create tests to enable order entry and reporting.</p>
</div>
@endsection
