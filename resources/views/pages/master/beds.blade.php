@extends('layouts.app')

@section('title', 'Hospital Beds')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <x-master-nav />

        <livewire:master.bed-list />
    </div>
@endsection
