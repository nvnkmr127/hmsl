@extends('layouts.app')

@section('title', 'IPD Admissions')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">IPD Admissions</h1>

        <x-counter-nav />

        <livewire:counter.ipd-admissions />
    </div>
@endsection
