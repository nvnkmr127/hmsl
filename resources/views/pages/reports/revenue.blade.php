@extends('layouts.app')

@section('title', 'Financial Revenue Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-black text-gray-800 dark:text-white uppercase tracking-tighter">Business Intelligence</h1>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="p-3 rounded-2xl bg-white dark:bg-gray-800 shadow-sm text-gray-400 hover:text-indigo-600 border border-gray-100 dark:border-gray-700/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                </button>
            </div>
        </div>
        
        <x-master-nav />

        <livewire:reports.revenue-dashboard />
    </div>
@endsection
