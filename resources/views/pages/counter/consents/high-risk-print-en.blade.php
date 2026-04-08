@extends('layouts.print')

@section('title', 'High Risk Consent - ' . $patient->full_name)

@section('content')
@include('partials.consents.high-risk-english', ['patient' => $patient, 'hospital' => $hospital, 'doctorName' => $doctorName])
@endsection

