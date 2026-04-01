@extends('layouts.app')

@section('title', 'Doctor Dashboard')

@section('content')
<x-page-header title="Doctor" subtitle="Appointments and consultation queue">
    <x-slot:actions>
        @can('edit case sheets')
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-primary">Consultation Desk</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Today">
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">My Pending</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['doctorPendingToday'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OPD Tokens</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['opdToday'] }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Shortcuts">
        <div class="space-y-2">
            @can('edit case sheets')
            <a href="{{ route('doctor.appointments.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Appointments</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Today</span>
            </a>
            @endcan
            @can('view patients')
            <a href="{{ route('doctor.patients.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Records</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Patients</span>
            </a>
            @endcan
        </div>
    </x-card>

    <x-card title="Notes">
        <div class="text-sm text-gray-600 dark:text-gray-300">
            Use Consultation Desk to manage the live queue and complete visits.
        </div>
    </x-card>
</div>
@endsection

