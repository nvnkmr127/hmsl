@extends('layouts.app')

@section('title', 'Discharge Summary')

@section('content')
<x-page-header title="Discharge Summary" subtitle="Review discharge details and print the summary." :back="route('discharge.index')" backLabel="Discharge">
    <x-slot name="actions">
        <a class="btn btn-primary" target="_blank" href="{{ route('discharge.print', $admission->id) }}">Print</a>
    </x-slot>
</x-page-header>

<div class="glass-card p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Patient</h3>
            <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p class="font-bold">{{ $admission->patient->full_name }}</p>
                <p>UHID: <span class="font-semibold">{{ $admission->patient->uhid }}</span></p>
                <p>Phone: <span class="font-semibold">{{ $admission->patient->phone }}</span></p>
            </div>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Admission</h3>
            <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p>Admission No: <span class="font-semibold">{{ $admission->admission_number }}</span></p>
                <p>Ward/Bed: <span class="font-semibold">{{ $admission->bed?->ward?->name ?? 'N/A' }} / {{ $admission->bed?->bed_number ?? 'N/A' }}</span></p>
                <p>Doctor: <span class="font-semibold">{{ $admission->doctor?->full_name ?? 'Unassigned' }}</span></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Dates</h3>
            <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p>Admitted: <span class="font-semibold">{{ $admission->admission_date?->format('d M Y, H:i') ?? '—' }}</span></p>
                <p>Discharged: <span class="font-semibold">{{ $admission->discharge_date?->format('d M Y, H:i') ?? '—' }}</span></p>
                <p>Status: <span class="font-semibold">{{ $admission->status }}</span></p>
            </div>
        </div>
        <div class="glass-card p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Reason</h3>
            <p class="text-sm text-slate-700 dark:text-slate-200">
                {{ $admission->reason_for_admission ?: '—' }}
            </p>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Discharge Notes</h3>
        <p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">
            {{ $admission->notes ?: '—' }}
        </p>
    </div>
</div>
@endsection
