@extends('layouts.app')

@section('title', 'Pharmacy Dashboard')

@section('content')
<x-page-header title="Pharmacy" subtitle="Dispense medicines and manage stock">
    <x-slot:actions>
        @can('view pharmacy')
        <a href="{{ route('pharmacy.index') }}" class="btn btn-primary">Pharmacy</a>
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
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">IPD Admitted</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['ipdAdmitted'] }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Work">
        <div class="space-y-2">
            @can('view pharmacy')
            <a href="{{ route('pharmacy.orders') }}" class="btn btn-secondary w-full justify-between">
                <span>Orders</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Process</span>
            </a>
            <a href="{{ route('pharmacy.stock') }}" class="btn btn-secondary w-full justify-between">
                <span>Medicine Stock</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Levels</span>
            </a>
            @endcan
        </div>
    </x-card>

    <x-card title="Inventory">
        <div class="space-y-2">
            @can('manage inventory')
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary w-full justify-between">
                <span>Stock</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Store</span>
            </a>
            @endcan
        </div>
    </x-card>
</div>
@endsection

