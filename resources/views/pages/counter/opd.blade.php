@extends('layouts.app')

@section('title', 'OPD Bookings')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Counter Operations</h1>

        <x-counter-nav />

        <livewire:counter.opd-booking :patient_id="$patient_id" />
    </div>
@endsection
