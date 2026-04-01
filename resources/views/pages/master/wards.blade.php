@extends('layouts.app')

@section('title', 'Wards & Beds')

@section('content')
    <div class="space-y-6">
        
        <x-master-nav />

        <livewire:master.ward-list />
    </div>
@endsection
