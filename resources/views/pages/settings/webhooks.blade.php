@extends('layouts.app')

@section('title', 'Webhook Integrations')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">API Webhooks</h1>
            <a href="{{ route('settings.webhooks.logs') }}" class="btn-secondary px-6 py-2.5 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span>Delivery Logs</span>
            </a>
        </div>
        
        <x-master-nav />

        <livewire:settings.webhook-endpoints />
    </div>
@endsection
