@extends('layouts.app')

@section('title', 'Reception Dashboard')

@section('content')
<x-page-header title="Reception" subtitle="Register patients, create tokens, and manage billing">
    <x-slot:actions>
        @can('view patients')
        <a href="{{ route('counter.patients.index') }}" class="btn btn-secondary">Patients</a>
        @endcan
        @can('view opd')
        <a href="{{ route('counter.opd.index') }}" class="btn btn-primary">New Token</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Today">
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OPD Tokens</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['opdToday'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Bills</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['billsToday'] }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Visits">
        <div class="space-y-2">
            @can('view opd')
            <a href="{{ route('counter.opd.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Outpatient Counter</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Queue</span>
            </a>
            @endcan
            @can('view ipd')
            <a href="{{ route('counter.ipd.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Inpatient Admissions</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">IPD</span>
            </a>
            @endcan
        </div>
    </x-card>

    <x-card title="Billing">
        <div class="space-y-2">
            @can('view billing')
            <a href="{{ route('billing.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Billing Desk</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Invoices</span>
            </a>
            @endcan
        </div>
    </x-card>
</div>
@endsection

