@extends('layouts.app')

@section('title', 'Pharmacy Inventory')

@section('content')
    <div class="space-y-6">
        
        <x-master-nav />

        <livewire:master.medicine-list />
    </div>
@endsection
