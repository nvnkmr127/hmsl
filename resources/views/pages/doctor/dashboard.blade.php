@extends('layouts.app')

@section('title', 'Doctor Consultation Desk')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Clinical Desk</h1>
        <livewire:doctor.doctor-dashboard-stats />
        
        <div>
            <livewire:reports.discount-audit-report :isDashboard="true" />
        </div>
    </div>
@endsection
