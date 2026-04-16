@extends('layouts.app')

@section('title', 'Admission Form')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('counter.ipd.index') }}" class="p-2 rounded-xl bg-white dark:bg-gray-800 shadow-sm text-gray-400 hover:text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">New Admission</h1>
        </div>

        <livewire:counter.ipd-admission-form />
    </div>
@endsection
