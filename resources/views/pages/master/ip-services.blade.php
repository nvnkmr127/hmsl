@extends('layouts.app')

@section('title', 'IP Services Master')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <x-master-nav />

        <livewire:master.ip-service-list />
    </div>
@endsection
