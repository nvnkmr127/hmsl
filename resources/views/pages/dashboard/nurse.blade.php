@extends('layouts.app')

@section('title', 'Nurse Dashboard')

@section('content')
<x-page-header title="Nursing" subtitle="Inpatient care and vitals updates">
    <x-slot:actions>
        @can('view ipd')
        <a href="{{ route('counter.ipd.index') }}" class="btn btn-primary">Inpatients</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Today">
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">IPD Admitted</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['ipdAdmitted'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OPD Pending</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['opdPendingToday'] }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Shortcuts">
        <div class="space-y-2">
            @can('view patients')
            <a href="{{ route('counter.patients.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Patients</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Search</span>
            </a>
            @endcan
            @can('view ipd')
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Inpatient</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Rounds</span>
            </a>
            @endcan
        </div>
    </x-card>

    <x-card title="Notes">
        <div class="text-sm text-gray-600 dark:text-gray-300">
            Use the inpatient screens to update vitals and nursing notes for admitted patients.
        </div>
    </x-card>
</div>
@endsection

