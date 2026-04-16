@extends('layouts.app')

@section('title', 'Patient Visits Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('reports.index') }}" class="p-2 rounded-xl bg-white dark:bg-slate-800 shadow-sm text-slate-400 hover:text-primary-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Patient Visits</h1>
    </div>

    <livewire:reports.visit-report />
</div>
@endsection
