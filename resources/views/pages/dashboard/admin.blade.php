@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header title="Dashboard" subtitle="Today’s overview across modules">
    <x-slot:actions>
        @can('view opd')
        <a href="{{ route('counter.opd.index') }}" class="btn btn-primary">New Visit</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <x-card title="Today">
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OPD Tokens</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['opdToday'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OPD Pending</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['opdPendingToday'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">IPD Admitted</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['ipdAdmitted'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Revenue (Paid)</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">₹{{ number_format($metrics['revenueToday'], 2) }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Quick Actions" subtitle="Role-based shortcuts">
        <div class="space-y-2">
            @can('view patients')
            <a href="{{ route('counter.patients.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Patients</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">List</span>
            </a>
            @endcan
            @can('edit case sheets')
            <a href="{{ route('doctor.dashboard') }}" class="btn btn-secondary w-full justify-between">
                <span>Consultation Desk</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Doctor</span>
            </a>
            @endcan
            @can('view opd')
            <a href="{{ route('counter.opd.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Outpatient</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Tokens</span>
            </a>
            @endcan
            @can('view ipd')
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Inpatient</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Admissions</span>
            </a>
            @endcan
            @can('view billing')
            <a href="{{ route('billing.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Billing</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Invoices</span>
            </a>
            @endcan
            @can('admit patients')
            <a href="{{ route('discharge.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Discharge</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Summary</span>
            </a>
            @endcan
        </div>
    </x-card>

    <x-card title="Admin" subtitle="Configuration and oversight">
        <div class="space-y-2">
            @can('view reports')
            <a href="{{ route('reports.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Reports</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Stats</span>
            </a>
            @endcan
            @can('manage master data')
            <a href="{{ route('master.doctors.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Staff</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Master</span>
            </a>
            @endcan
            @can('manage users')
            <a href="{{ route('master.users.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Users</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Access</span>
            </a>
            @endcan
            @can('manage settings')
            <a href="{{ route('settings.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Settings</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">System</span>
            </a>
            @endcan
        </div>
    </x-card>
</div>
@endsection
