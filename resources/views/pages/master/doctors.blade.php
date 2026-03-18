@extends('layouts.app')

@section('title', 'Doctors Directory')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Master Data</h1>
        
        <x-master-nav />

        <livewire:master.doctor-list />
    </div>
@endsection
