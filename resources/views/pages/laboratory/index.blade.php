@extends('layouts.app')

@section('title', 'Laboratory Tracking')

@section('content')
<x-breadcrumb />

<x-page-header title="Laboratory Tracking" subtitle="Manage clinical tests, biological samples, and diagnostic reports.">
    <x-slot name="actions">
        <button class="btn btn-secondary">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            Print Labels
        </button>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            New Sample
        </button>
    </x-slot>
</x-page-header>

<livewire:laboratory.laboratory-orders />
@endsection
