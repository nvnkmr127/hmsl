@extends('layouts.app')

@section('title', 'Patient Management')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Counter Operations</h1>

        <x-counter-nav />

        <livewire:counter.patient-list />
    </div>
@endsection
