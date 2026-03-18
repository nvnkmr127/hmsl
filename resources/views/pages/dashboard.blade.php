@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">{{ now()->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('counter.opd.index') }}" class="btn btn-primary self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Appointment
    </a>
</div>

<!-- KPI Row -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['OPD Today',     '42',   '+12%',  'badge-success', '#7c3aed', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['IPD Beds',      '18/50','72% full','badge-gray',   '#111827', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['Lab Queue',     '12',   'Pending','badge-warning', '#7c3aed', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['Revenue',       '₹24.5K','Today', 'badge-violet', '#7c3aed', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ] as [$label, $value, $tag, $badgeCls, $iconColor, $path])
    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:rgba(124,58,237,0.1)">
                <svg class="w-5 h-5" style="color:{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $path }}"/>
                </svg>
            </div>
            <span class="badge {{ $badgeCls }}">{{ $tag }}</span>
        </div>
        <p class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none mb-1">{{ $value }}</p>
        <p class="text-sm font-600 text-gray-500 dark:text-gray-400">{{ $label }}</p>
    </div>
    @endforeach
</div>

<!-- Main grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <!-- Active Queue -->
    <div class="xl:col-span-2 card overflow-hidden">
        <div class="panel-hd">
            <div>
                <p class="panel-title">Active Queue</p>
                <p class="panel-subtitle">Live OPD consultation status</p>
            </div>
            <a href="{{ route('public.queue') }}" class="btn btn-outline text-xs py-1.5 px-3">View Queue</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-white/5">
            @foreach(range(1,5) as $i)
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                     style="background:#111827">{{ $i }}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Patient #{{ 1024 + $i }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Token T-{{ 100 + $i }} · Room 4B</p>
                </div>
                <span class="badge badge-success flex-shrink-0">With Doctor</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Right column -->
    <div class="space-y-5">

        <!-- Occupancy -->
        <div class="card p-5">
            <p class="panel-title mb-5">Facility Occupancy</p>
            <div class="space-y-4">
                @foreach([['Critical Care', 40], ['General Ward', 72], ['ICU', 55]] as [$ward, $pct])
                <div>
                    <div class="flex justify-between mb-1.5">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $ward }}</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $pct }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-5">
            <p class="panel-title mb-4">Quick Actions</p>
            <div class="space-y-1">
                @foreach([
                    [route('counter.patients.index'), 'Register Patient',  'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                    [route('billing.index'),          'Create Invoice',    'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    [route('reports.index'),          'View Reports',      'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ] as [$href, $label, $path])
                <a href="{{ $href }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-all">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#ede9fe">
                        <svg class="w-4 h-4" style="color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/>
                        </svg>
                    </span>
                    {{ $label }}
                    <svg class="w-4 h-4 ml-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
