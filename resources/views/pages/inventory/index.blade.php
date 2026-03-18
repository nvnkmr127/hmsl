@extends('layouts.app')

@section('title', 'Inventory & Logistics')

@section('content')
<div class="section-header">
    <div>
        <h1 class="section-title">Inventory & Logistics</h1>
        <p class="section-subtitle">Monitor stock levels, manage suppliers, and track medical equipment.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-secondary">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Purchase Order
        </button>
        <button class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add New Item
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="stat-card">
        <div class="stat-icon bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Total SKUs</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">1,240</h3>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Out of Stock</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">14</h3>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Recent Arrivals</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">85</h3>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Vendor Payments</p>
        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-2">12</h3>
    </div>
</div>

<div class="glass-card">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Inventory Status</h3>
    </div>
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Item Code</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Item Name</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Category</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Current Stock</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Min Level</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach (['Syringe 5ml', 'Paracetamol 500mg', 'Surgical Gloves', 'Mask N95', 'Hand Sanitizer'] as $item)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <td class="px-6 py-4 text-xs font-mono font-bold text-slate-500">SKU-{{ 1000 + $loop->index }}</td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white">{{ $item }}</td>
                    <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">Medical Supplies</td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold {{ $loop->index == 1 ? 'text-red-500' : 'text-slate-800 dark:text-white' }}">
                            {{ rand(5, 500) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">50 units</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
