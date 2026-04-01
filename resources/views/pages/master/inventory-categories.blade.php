@extends('layouts.app')

@section('title', 'Inventory Categories')

@section('content')
    <div class="space-y-6">
        <x-master-nav />
        <livewire:master.inventory-category-list />
    </div>
@endsection
