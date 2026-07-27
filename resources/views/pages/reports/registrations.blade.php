@extends('layouts.app')

@section('title', 'Registration Metrics Report')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #google-geo-map {
        width: 100% !important;
        height: 520px !important;
        min-height: 520px !important;
        background-color: #0b0f19 !important;
        border-radius: 1.5rem !important;
    }
    @keyframes shopifyPulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.08); box-shadow: 0 0 0 18px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .shopify-pin-emerald {
        animation: shopifyPulse 2s infinite ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    window.googleMapsFailed = false;
    window.gm_authFailure = function() {
        console.warn('Google Maps API key unbilled or invalid. Falling back to Leaflet Map engine.');
        window.googleMapsFailed = true;
        window.dispatchEvent(new Event('google-maps-failed'));
    };
    window.initGoogleMap = function() {
        window.googleMapsLoaded = true;
        window.dispatchEvent(new Event('google-maps-loaded'));
    };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=visualization&callback=initGoogleMap" async defer></script>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('reports.index') }}" class="p-2 rounded-xl bg-white dark:bg-slate-800 shadow-sm text-slate-400 hover:text-primary-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Registration Metrics</h1>
    </div>

    <livewire:reports.registration-report />
</div>
@endsection
