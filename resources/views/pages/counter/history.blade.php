@extends('layouts.app')

@section('title', 'Patient History')

@section('content')

{{-- Back breadcrumb --}}
<div class="flex items-center gap-3 mb-8">
    <a href="{{ route('counter.patients.index') }}"
       class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors group">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Patient Registry
    </a>
    <span class="text-slate-300 dark:text-slate-700">/</span>
    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Clinical History</span>
</div>

<livewire:counter.patient-history :id="request()->route('id')" />

@endsection
