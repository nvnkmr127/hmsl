@extends('layouts.app')

@section('title', 'Admission Layouts')

@section('content')
    <div class="space-y-6">
        <x-breadcrumb />

        <x-master-nav />
        <livewire:master.clinical-template-list />
    </div>
@endsection
