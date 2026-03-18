@extends('layouts.app')

@section('title', 'Laboratory Tracking')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Laboratory Tracking</h1>
        <p class="section-subtitle">Manage clinical tests, biological samples, and diagnostic reports.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-secondary">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            Print Labels
        </button>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            New Sample
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="stat-card">
        <div class="stat-icon bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Queued Tests</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">24</h3>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">In Process</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">8</h3>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Ready Reports</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">156</h3>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Home Collections</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">3</h3>
    </div>
</div>

<div class="glass-card">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Active Test Samples</h3>
    </div>
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Barcode / SID</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Patient Details</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Test Name</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Time</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach (range(1, 5) as $i)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">LAB-SX-{{ 500 + $i }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-slate-800 dark:text-white">John Doe #{{ $i }}</p>
                        <p class="text-[10px] text-slate-400">Male, 45 yrs</p>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 font-medium">
                        Complete Blood Count (CBC)
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-500">
                        {{ now()->subMinutes(15 * $i)->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4">
                        <button class="btn btn-secondary !px-2.5 !py-1.5 text-[10px]">
                            Upload Results
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
