@extends('layouts.print-telugu')

@section('title', 'High Risk Consent - ' . $patient->full_name)

@section('content')
@include('partials.consents.high-risk-telugu', ['patient' => $patient, 'hospital' => $hospital, 'doctorName' => $doctorName])
@endsection

