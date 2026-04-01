@extends('layouts.app')

@section('title', 'Laboratory Tests')

@section('content')
<x-page-header title="Laboratory Tests" subtitle="Manage test catalog, pricing, and sample requirements." :back="route('laboratory.index')" backLabel="Laboratory">
    <x-slot name="actions">
        <button class="btn btn-primary">Add Test</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Test Catalog</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No test definitions available yet. Create tests to enable order entry and reporting.</p>
</div>
@endsection
