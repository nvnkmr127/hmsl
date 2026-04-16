@extends('layouts.app')

@section('title', 'Inventory & Logistics')

@section('content')
<x-breadcrumb />

<x-page-header title="Inventory & Logistics" subtitle="Monitor stock levels, manage suppliers, and track medical equipment.">
    <x-slot name="actions">
        <button class="btn btn-secondary">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Purchase Order
        </button>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add New Item
        </button>
    </x-slot>
</x-page-header>

<livewire:inventory.inventory-manager />
@endsection
