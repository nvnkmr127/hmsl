@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <div class="space-y-6">
        
        <x-master-nav />

        <livewire:master.department-list />
    </div>
@endsection
