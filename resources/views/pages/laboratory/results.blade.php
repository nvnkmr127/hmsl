@extends('layouts.app')

@section('title', 'Laboratory Results')

@section('content')
<x-page-header title="Laboratory Results" subtitle="Track result validation and publication for completed tests." :back="route('laboratory.index')" backLabel="Laboratory">
    <x-slot name="actions">
        <button class="btn btn-primary">Upload Result</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Result Worklist</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No pending results. Integrated lab entries will appear here for review and release.</p>
</div>
@endsection
