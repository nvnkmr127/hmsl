@extends('layouts.app')

@section('title', 'Laboratory Tests')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <x-master-nav />

        <livewire:master.lab-list />
    </div>
@endsection
