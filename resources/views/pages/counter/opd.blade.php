@extends('layouts.app')

@section('title', 'OPD Bookings')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Counter Operations</h1>
        
        <div class="flex flex-wrap gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-4 mb-8">
            <a href="{{ route('counter.patients.index') }}" class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('counter.patients.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                Patient Registry
            </a>
            <a href="{{ route('counter.opd.index') }}" class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('counter.opd.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                OPD Bookings
            </a>
            <a href="#" class="px-6 py-3 rounded-2xl text-sm font-bold text-gray-400 cursor-not-allowed uppercase tracking-widest">
                Billing (Planned)
            </a>
        </div>

        <livewire:counter.opd-booking :patient_id="$patient_id" />
    </div>
@endsection
