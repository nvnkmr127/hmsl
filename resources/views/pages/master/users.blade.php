@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<x-breadcrumb />

<x-page-header title="User Management" subtitle="Manage hospital staff access, roles, and permissions.">
    <x-slot name="actions">
        <button class="btn btn-primary" @click="$dispatch('create-user')">Add New User</button>
    </x-slot>
</x-page-header>

<div class="space-y-6">
    <livewire:master.user-list />
    <livewire:master.user-form />
</div>
@endsection
