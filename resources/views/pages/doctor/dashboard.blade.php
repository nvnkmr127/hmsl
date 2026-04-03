@extends('layouts.app')

@section('title', 'Doctor Consultation Desk')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Clinical Desk</h1>
        <livewire:doctor.doctor-dashboard-stats />
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <livewire:doctor.consultation-desk />
            </div>
            <div class="lg:col-span-1">
                <livewire:doctor.receptionist-list />
            </div>
        </div>
    </div>
@endsection
