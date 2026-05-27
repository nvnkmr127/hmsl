@extends('layouts.app')

@section('title', 'Discharge Summary')

@section('content')
    <livewire:discharge.discharge-summary-form :admission="$admission" />
@endsection
