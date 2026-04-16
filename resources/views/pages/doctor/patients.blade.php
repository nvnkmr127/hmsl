@extends('layouts.app')

@section('title', 'Patient Records')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Patient Records</h1>
            <button @click="$dispatch('open-patient-form')" class="btn btn-primary px-6">
                Add New Patient
            </button>
        </div>
        
        <livewire:counter.patient-list />
        <livewire:counter.patient-form />
    </div>
@endsection
