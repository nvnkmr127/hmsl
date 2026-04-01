@extends('layouts.app')

@section('title', 'Lab Dashboard')

@section('content')
<x-page-header title="Laboratory" subtitle="Samples and results">
    <x-slot:actions>
        @can('view lab')
        <a href="{{ route('laboratory.index') }}" class="btn btn-primary">Lab</a>
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
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">OPD Pending</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metrics['opdPendingToday'] }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Work">
        <div class="space-y-2">
            @can('view lab')
            <a href="{{ route('laboratory.tests') }}" class="btn btn-secondary w-full justify-between">
                <span>Tests</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Queue</span>
            </a>
            <a href="{{ route('laboratory.results') }}" class="btn btn-secondary w-full justify-between">
                <span>Results</span>
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Update</span>
            </a>
            @endcan
        </div>
    </x-card>

    <x-card title="Notes">
        <div class="text-sm text-gray-600 dark:text-gray-300">
            Use Tests for pending orders and Results to update completion status.
        </div>
    </x-card>
</div>
@endsection

