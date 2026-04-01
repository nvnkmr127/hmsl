@extends('layouts.app')

@section('title', 'Hosptial Beds')

@section('content')
    <div class="space-y-6">
        <x-master-nav />

        <livewire:master.bed-list />
    </div>
@endsection
