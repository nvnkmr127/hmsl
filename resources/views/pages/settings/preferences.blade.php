@extends('layouts.app')

@section('title', 'System Preferences')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">System Settings</h1>
        
        <div class="flex flex-wrap gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-4 mb-8">
            <a href="{{ route('settings.index') }}" class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('settings.index') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                Hospital Details
            </a>
            <a href="{{ route('settings.preferences') }}" class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('settings.preferences') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                System Preferences
            </a>
            <a href="{{ route('settings.invoice') }}" class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('settings.invoice') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                Invoice & Print
            </a>
        </div>

        <livewire:settings.system-preferences />
    </div>
@endsection
