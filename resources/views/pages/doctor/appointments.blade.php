@extends('layouts.app')

@section('title', 'Appointment Management')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Appointments</h1>
                <p class="text-sm text-gray-500 font-medium">View and manage your patient schedule.</p>
            </div>
            <div class="flex gap-3">
                <button @click="$dispatch('open-scheduler')" class="btn btn-primary px-6">
                    Schedule New
                </button>
                <livewire:doctor.receptionist-list />
            </div>
        </div>
        
        <livewire:doctor.appointment-management />
        <livewire:doctor.appointment-scheduler />
    </div>
@endsection
