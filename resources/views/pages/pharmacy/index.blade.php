@extends('layouts.app')

@section('title', 'Pharmacy Management')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Pharmacy Management</h1>
        <p class="section-subtitle">Track prescriptions, dispense medicines, and manage pharmacy stock.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Export Stock
        </button>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            New Dispense
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="stat-card group">
        <div class="stat-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Pending Prescriptions</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">12</h3>
    </div>
    
    <div class="stat-card group">
        <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Dispensed Today</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">48</h3>
    </div>

    <div class="stat-card group">
        <div class="stat-icon bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Low Stock Items</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">5</h3>
    </div>

    <div class="stat-card group">
        <div class="stat-icon bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Today's Revenue</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">$2,450</h3>
    </div>
</div>

<div class="glass-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Recent Prescriptions</h3>
        <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-widest">View All</a>
    </div>
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Patient</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Doctor</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Items</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Status</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach (range(1, 5) as $i)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-slate-800 dark:text-white">Patient #{{ 202400 + $i }}</p>
                        <p class="text-[10px] text-slate-400 font-medium">OPD-{{ rand(100, 999) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Dr. Smith Johnson</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ rand(1, 4) }} Medicines</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            Pending
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button class="btn btn-ghost p-2">
                             <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
