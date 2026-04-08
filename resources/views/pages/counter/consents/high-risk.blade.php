@extends('layouts.app')

@section('title', 'High Risk Consent')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-page-header title="High Risk Consent" subtitle="Telugu form with prefilled patient details" :back="route('counter.patients.history', ['id' => $patient->id])" backLabel="Patient History">
        <x-slot:actions>
            <a target="_blank" href="{{ route('counter.patients.consents.high-risk.print', ['id' => $patient->id]) }}" class="btn btn-primary">
                Print
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        @include('partials.consents.high-risk-telugu', ['patient' => $patient, 'hospital' => $hospital, 'doctorName' => $doctorName])
    </x-card>
</div>
@endsection

