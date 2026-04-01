@extends('layouts.app') {{-- We can create a guest/screen layout later if needed --}}

@section('title', 'Live OPD Queue')

@section('content')
    <div class="space-y-12">
        <div class="text-center">
            <h1 class="text-5xl font-black text-gray-800 dark:text-white uppercase tracking-tighter mb-4">Patient Queue Status</h1>
            <p class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.3em]">Real-Time Updates Tracking Every Consultation</p>
        </div>

        <livewire:front.queue-monitor />
    </div>
@endsection
