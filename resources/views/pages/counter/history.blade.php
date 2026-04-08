@extends('layouts.app')

@section('title', 'Patient History')

@section('content')


<livewire:counter.patient-history :id="request()->route('id')" />
<livewire:counter.patient-form />

@endsection
