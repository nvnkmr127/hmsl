@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header title="Dashboard" subtitle="Today’s overview across modules">
    <x-slot:actions>
        @can('view opd')
        <a href="{{ route('counter.opd.index') }}" class="btn btn-primary">New Visit</a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="space-y-8">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <x-card title="Today’s Highlights">
            <div class="grid grid-cols-2 gap-4">
                <div class="p-5 rounded-3xl bg-violet-500/5 border border-violet-100/50 dark:border-violet-900/30">
                    <p class="text-[10px] font-black text-violet-600 uppercase tracking-[0.2em] mb-1">OPD Tokens</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $metrics['opdToday'] }}</p>
                </div>
                <div class="p-5 rounded-3xl bg-amber-500/5 border border-amber-100/50 dark:border-amber-900/30">
                    <p class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-1">OPD Pending</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $metrics['opdPendingToday'] }}</p>
                </div>
                <div class="p-5 rounded-3xl bg-sky-500/5 border border-sky-100/50 dark:border-sky-900/30">
                    <p class="text-[10px] font-black text-sky-600 uppercase tracking-[0.2em] mb-1">IPD Admitted</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $metrics['ipdAdmitted'] }}</p>
                </div>
                <div class="p-5 rounded-3xl bg-emerald-500/5 border border-emerald-100/50 dark:border-emerald-900/30">
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-1">Revenue Today</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">₹{{ number_format($metrics['revenueToday']) }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Quick Management" subtitle="Role-based shortcuts">
            <div class="grid grid-cols-1 gap-2">
                @can('view patients')
                <a href="{{ route('counter.patients.index') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-violet-50 dark:hover:bg-violet-900/20 group transition-all border border-transparent hover:border-violet-200">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Child Registry</span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endcan
                @can('edit case sheets')
                <a href="{{ route('doctor.dashboard') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-violet-50 dark:hover:bg-violet-900/20 group transition-all border border-transparent hover:border-violet-200">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Pediatrician Desktop</span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endcan
                @can('view billing')
                <a href="{{ route('billing.index') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-violet-50 dark:hover:bg-violet-900/20 group transition-all border border-transparent hover:border-violet-200">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Billing Desk</span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endcan
            </div>
        </x-card>

        <x-card title="System Oversight" subtitle="Configure hospital modules">
            <div class="grid grid-cols-2 gap-2">
                @can('manage master data')
                <a href="{{ route('master.doctors.index') }}" class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-all text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase">Staff</p>
                </a>
                @endcan
                @can('manage users')
                <a href="{{ route('master.users.index') }}" class="p-3 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-all text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase">Users</p>
                </a>
                @endcan
                @can('manage settings')
                <a href="{{ route('settings.index') }}" class="col-span-2 p-3 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-all text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase">System Settings</p>
                </a>
                @endcan
            </div>
        </x-card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Revenue Performance" subtitle="Daily collections (Last 7 Days)">
            <div id="revenueChart" class="h-64"></div>
        </x-card>
        <x-card title="Patient Footfall" subtitle="Daily OPD Tokens (Last 7 Days)">
            <div id="tokChart" class="h-64"></div>
        </x-card>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const commonOptions = {
        chart: {
            height: 250,
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Inter, sans-serif'
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 4 },
        xaxis: {
            categories: @json($metrics['trend']['dates']),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        grid: {
            borderColor: 'rgba(156, 163, 175, 0.1)',
            strokeDashArray: 4
        }
    };

    const revChart = new ApexCharts(document.querySelector("#revenueChart"), {
        ...commonOptions,
        colors: ['#10B981'],
        series: [{
            name: 'Revenue (₹)',
            data: @json($metrics['trend']['revenue'])
        }],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0,
                stops: [0, 90, 100]
            }
        },
        yaxis: {
            labels: {
                formatter: value => '₹' + value.toLocaleString()
            }
        }
    });
    revChart.render();

    const tokChart = new ApexCharts(document.querySelector("#tokChart"), {
        ...commonOptions,
        colors: ['#7C3AED'],
        series: [{
            name: 'Tokens',
            data: @json($metrics['trend']['tokens'])
        }],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0,
                stops: [0, 90, 100]
            }
        }
    });
    tokChart.render();
});
</script>
@endpush
@endsection
