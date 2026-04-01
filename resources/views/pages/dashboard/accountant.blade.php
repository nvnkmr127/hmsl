@extends('layouts.app')

@section('title', 'Accounts Dashboard')

@section('content')
<x-page-header title="Accounts" subtitle="Billing, payments, and reports">
    <x-slot:actions>
        @can('view billing')
        <a href="{{ route('billing.index') }}" class="btn btn-primary">Billing</a>
        @endcan
        @can('view reports')
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reports</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-card title="Today">
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Bills</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['billsToday'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-white/5">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Revenue (Paid)</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">₹{{ number_format($metrics['revenueToday'], 2) }}</p>
            </div>
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

    <x-card title="Reports">
        <div class="space-y-2">
            @can('view reports')
            <a href="{{ route('reports.revenue') }}" class="btn btn-secondary w-full justify-between">
                <span>Revenue Stats</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">View</span>
            </a>
            @endcan
        </div>
    </x-card>
</div>
@endsection

