@extends('layouts.app')

@section('title', 'Lab Utilization Report')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-black text-gray-800 dark:text-white uppercase tracking-tighter">Clinical Intelligence</h1>
        </div>
        
        <div class="p-20 text-center glass-card">
            <div class="mx-auto w-24 h-24 bg-sky-100 dark:bg-sky-900/30 rounded-3xl flex items-center justify-center text-sky-600 mb-8">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3v6.75M14.25 3v6.75M21 21H3l5.625-11.25H15.375L21 21z"/></svg>
            </div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-4">Lab Utilization Report Coming Soon</h2>
            <p class="text-gray-500 max-w-sm mx-auto font-medium">Aggregated lab throughput and test-wise performance analytics are being processed.</p>
        </div>
    </div>
@endsection
