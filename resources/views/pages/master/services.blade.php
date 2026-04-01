@extends('layouts.app')

@section('title', 'Hospital Services')

@section('content')
    <div class="space-y-6">
        <x-master-nav />

        <livewire:master.service-list />
    </div>
@endsection

