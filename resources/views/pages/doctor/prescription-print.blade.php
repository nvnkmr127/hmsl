@extends('layouts.print')

@section('title', 'Prescription — ' . $prescription->consultation->patient->full_name)

@section('content')
<div class="header">
    <div class="hospital-info">
        <h1>{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }}</h1>
        <p>{{ \App\Models\Setting::get('hospital_tagline', '') }}</p>
        <p>{{ \App\Models\Setting::get('hospital_address', '') }}, {{ \App\Models\Setting::get('hospital_city', '') }}</p>
        <p>📞 {{ \App\Models\Setting::get('hospital_phone', '') }}</p>
    </div>
    <div style="text-align:right">
        <p style="font-size:22pt; font-weight:700; color:#4F46E5; margin:0; font-style:italic">℞ Prescription</p>
        <p style="color:#888; margin:4px 0">Date: {{ $prescription->created_at->format('d/m/Y') }}</p>
        <p style="margin:2px 0"><strong>Dr. {{ $prescription->doctor->full_name }}</strong></p>
        <p style="color:#666; font-size:9pt;">{{ $prescription->doctor->qualification ?? '' }} | {{ $prescription->doctor->specialization ?? '' }}</p>
    </div>
</div>

<div class="content">
    {{-- Patient Info --}}
    <div style="background:#f8f9ff; border-radius:8px; padding:12px 16px; margin-bottom:20px; display:flex; gap:40px;">
        <div>
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; margin:0 0 4px;">Patient</p>
            <p style="font-weight:700; font-size:13pt; margin:0 0 2px;">{{ $prescription->patient->full_name }}</p>
            <p style="color:#555; margin:0">UHID: {{ $prescription->patient->uhid }} &nbsp;|&nbsp;
               Age/Sex: {{ $prescription->patient->age }}Y / {{ $prescription->patient->gender }}</p>
        </div>
        <div>
            <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; margin:0 0 4px;">Visit</p>
            <p style="margin:0 0 2px;">Token #{{ $prescription->consultation->token_number }}</p>
            <p style="color:#555; margin:0">{{ $prescription->consultation->consultation_date->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Chief Complaint --}}
    @if($prescription->chief_complaint)
    <div style="margin-bottom:16px;">
        <p style="font-size:8pt; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.08em; margin:0 0 4px; border-bottom:1px solid #e5e7eb; padding-bottom:4px;">Chief Complaint</p>
        <p style="margin:8px 0 0; color:#374151;">{{ $prescription->chief_complaint }}</p>
    </div>
    @endif

    {{-- Diagnosis --}}
    @if($prescription->diagnosis)
    <div style="margin-bottom:16px; background:#fff7ed; border-left:3px solid #f59e0b; padding:10px 14px; border-radius:0 6px 6px 0;">
        <p style="font-size:8pt; font-weight:700; color:#92400e; text-transform:uppercase; margin:0 0 4px;">Diagnosis</p>
        <p style="margin:0; font-weight:600; color:#374151;">{{ $prescription->diagnosis }}</p>
    </div>
    @endif

    {{-- Medicines --}}
    @if($prescription->medicines && count($prescription->medicines) > 0)
    <div style="margin-bottom:20px;">
        <p style="font-size:8pt; font-weight:700; color:#4F46E5; text-transform:uppercase; letter-spacing:0.1em; margin:0 0 8px; border-bottom:2px solid #4F46E5; padding-bottom:6px;">℞ Medicines</p>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine</th>
                    <th>Dose</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prescription->medicines as $i => $med)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $med['name'] }}</strong></td>
                    <td>{{ $med['dose'] ?? '—' }}</td>
                    <td>{{ $med['frequency'] ?? '—' }}</td>
                    <td>{{ $med['duration'] ?? '—' }}</td>
                    <td style="color:#666;">{{ $med['instructions'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Advice --}}
    @if($prescription->advice)
    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px 16px; margin-bottom:16px;">
        <p style="font-size:8pt; font-weight:700; color:#065f46; text-transform:uppercase; margin:0 0 4px;">General Advice</p>
        <p style="margin:0; color:#374151;">{{ $prescription->advice }}</p>
    </div>
    @endif

    {{-- Follow-up --}}
    @if($prescription->follow_up_date)
    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; display:inline-block;">
        <p style="font-size:8pt; font-weight:700; color:#1d4ed8; text-transform:uppercase; margin:0 0 2px;">Follow-up Date</p>
        <p style="font-weight:700; font-size:12pt; color:#1e3a8a; margin:0;">{{ $prescription->follow_up_date->format('d/m/Y') }}</p>
    </div>
    @endif

    {{-- Doctor Signature --}}
    <div style="margin-top:40px; display:flex; justify-content:flex-end;">
        <div style="text-align:center; min-width:200px; border-top:1px solid #374151; padding-top:8px;">
            <p style="margin:0; font-weight:700;">Dr. {{ $prescription->doctor->full_name }}</p>
            <p style="margin:2px 0; font-size:9pt; color:#666;">{{ $prescription->doctor->qualification ?? '' }}</p>
            <p style="margin:0; font-size:8pt; color:#888;">Reg. No: {{ $prescription->doctor->registration_number ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<div class="footer">
    <p>This prescription is valid for 30 days from the date of issue. Not valid if altered.</p>
    <p style="margin:4px 0;">{{ \App\Models\Setting::get('hospital_name', 'City Care Hospital') }} &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i') }}</p>
</div>
@endsection
