@extends('layouts.app')

@section('title', 'System Reports')

@section('content')
<div class="space-y-12 py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight uppercase">Analytical Intelligence</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400 font-medium tracking-wide">Select a module to view detailed performance metrics and history.</p>
        </div>
    </div>

    @php
        $colors = [
            'from-blue-500/10 to-indigo-500/10',
            'from-emerald-500/10 to-teal-500/10',
            'from-purple-500/10 to-fuchsia-500/10',
            'from-amber-500/10 to-orange-500/10',
            'from-rose-500/10 to-pink-500/10'
        ];
        
        $iconColors = [
            'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-indigo-500/30',
            'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-teal-500/30',
            'bg-gradient-to-br from-purple-500 to-fuchsia-600 shadow-fuchsia-500/30',
            'bg-gradient-to-br from-amber-500 to-orange-600 shadow-orange-500/30',
            'bg-gradient-to-br from-rose-500 to-pink-600 shadow-pink-500/30'
        ];
        $colorIndex = 0;
    @endphp

    @foreach($reportGroups as $group => $reports)
    <div class="space-y-8">
        <div class="flex items-center gap-6 relative">
            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-slate-900 dark:bg-slate-800 text-white shadow-xl">
                @if($group === 'Financial Flow')
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($group === 'Patient Flow')
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                @elseif($group === 'Clinical Excellence')
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                @else
                    <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $group }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-8 h-1 rounded-full bg-slate-200 dark:bg-slate-700"></span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">End-to-End Analytics</p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($reports as $report)
            @php 
                $hasRoute = Route::has($report['route']); 
                $bgClass = $colors[$colorIndex % count($colors)];
                $iconClass = $iconColors[$colorIndex % count($iconColors)];
                $colorIndex++;
            @endphp
            <a href="{{ $hasRoute ? route($report['route']) : '#' }}" 
               class="group relative bg-gradient-to-br {{ $bgClass }} border border-white/50 dark:border-white/5 backdrop-blur-xl rounded-[2.5rem] p-8 transition-all duration-500 hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-2 overflow-hidden {{ !$hasRoute ? 'opacity-50 cursor-not-allowed' : '' }}">
                
                {{-- Decorative Blurred Background Shape --}}
                <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full bg-white/40 dark:bg-white/10 blur-3xl group-hover:scale-150 transition-transform duration-700 ease-in-out"></div>

                <div class="relative z-10 flex items-center justify-between mb-8">
                    <div class="w-16 h-16 rounded-3xl {{ $iconClass }} flex items-center justify-center text-white shadow-lg group-hover:rotate-[10deg] group-hover:scale-110 transition-all duration-500">
                        @if($report['icon'] === 'revenue')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        @elseif($report['icon'] === 'dues')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($report['icon'] === 'visit')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($report['icon'] === 'doctor')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @elseif($report['icon'] === 'patient')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        @elseif($report['icon'] === 'bed')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        @elseif($report['icon'] === 'discount')
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                    </div>
                    @if($hasRoute)
                    <div class="w-12 h-12 rounded-full bg-white/50 dark:bg-slate-800/50 backdrop-blur-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500 translate-x-4 group-hover:translate-x-0 border border-white/20">
                        <svg class="w-5 h-5 text-slate-800 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7"/></svg>
                    </div>
                    @endif
                </div>

                <div class="relative z-10">
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-tight group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-slate-800 group-hover:to-slate-600 dark:group-hover:from-white dark:group-hover:to-slate-300 transition-all duration-500">{{ $report['title'] }}</h3>
                    <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-400/80 leading-relaxed">{{ $report['desc'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
