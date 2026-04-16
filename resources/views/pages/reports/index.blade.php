@extends('layouts.app')

@section('title', 'System Reports')

@section('content')
<div class="space-y-10 py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight uppercase">Analytical Intelligence</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400 font-medium tracking-wide">Select a module to view detailed performance metrics and history.</p>
        </div>
    </div>

    @foreach($reportGroups as $group => $reports)
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <h2 class="text-xs font-black text-primary-600 dark:text-primary-400 uppercase tracking-[0.3em]">{{ $group }}</h2>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reports as $report)
            @php $hasRoute = Route::has($report['route']); @endphp
            <a href="{{ $hasRoute ? route($report['route']) : '#' }}" 
               class="group relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 transition-all duration-300 hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 {{ !$hasRoute ? 'opacity-50 cursor-not-allowed' : '' }}">
                
                <div class="flex items-start justify-between">
                    <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/30 group-hover:text-primary-600 transition-colors duration-300">
                        @if($report['icon'] === 'revenue')
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($report['icon'] === 'dues')
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        @elseif($report['icon'] === 'visit')
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @elseif($report['icon'] === 'doctor')
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        @else
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                    </div>
                    @if($hasRoute)
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity translate-x-4 group-hover:translate-x-0 duration-300">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    @else
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg">COMING SOON</span>
                    @endif
                </div>

                <div class="mt-6">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition-colors">{{ $report['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $report['desc'] }}</p>
                </div>
                
                {{-- Decorative background element --}}
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary-500/5 rounded-full blur-2xl group-hover:bg-primary-500/10 transition-colors"></div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
