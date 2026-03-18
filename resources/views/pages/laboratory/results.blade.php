@extends('layouts.app')

@section('title', 'Laboratory Results')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Laboratory Results</h1>
        <p class="section-subtitle">Track result validation and publication for completed tests.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('laboratory.index') }}" class="btn btn-secondary">Back to Laboratory</a>
        <button class="btn btn-primary">Upload Result</button>
    </div>
</div>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Result Worklist</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No pending results. Integrated lab entries will appear here for review and release.</p>
</div>
@endsection
