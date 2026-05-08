@extends('layouts.app')

@section('title', 'Hospital Settings')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">System Settings</h1>
        
        <x-settings-nav />

        <livewire:settings.hospital-settings />
    </div>
@endsection
