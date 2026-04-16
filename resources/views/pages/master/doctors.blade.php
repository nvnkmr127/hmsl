@extends('layouts.app')

@section('title', 'Doctors Directory')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <x-master-nav />

        <livewire:master.doctor-list />
    </div>
@endsection
