@extends('layouts.app')

@section('title', 'Data Sync Settings')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Offline Sync</h1>
        </div>
        
        <x-settings-nav />

        <livewire:sync.sync-status :isFullPage="true" />
        
        <livewire:sync.sync-conflicts />
    </div>
@endsection
