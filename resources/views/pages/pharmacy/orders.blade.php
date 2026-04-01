@extends('layouts.app')

@section('title', 'Pharmacy Orders')

@section('content')
<x-page-header title="Pharmacy Orders" subtitle="Review incoming medicine requests and track fulfillment status." :back="route('pharmacy.index')" backLabel="Pharmacy">
    <x-slot name="actions">
        <button class="btn btn-primary">Create Order</button>
    </x-slot>
</x-page-header>

<div class="glass-card p-6">
    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-3">Order Queue</h3>
    <p class="text-sm text-slate-500 dark:text-slate-400">No active orders yet. Connect this page to your order process to start processing requests.</p>
</div>
@endsection
