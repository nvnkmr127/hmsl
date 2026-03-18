@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">User Management</h1>
        <p class="section-subtitle">Manage hospital staff access, roles, and permissions.</p>
    </div>
</div>

<div class="space-y-6">
    <livewire:master.user-list />
    <livewire:master.user-form />
</div>
@endsection
