@extends('layouts.app')

@section('title', 'API Management & Tokens')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8 pb-12">
        <!-- Page Header -->
        <div class="relative">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <a href="{{ route('settings.index') }}" 
                        class="group flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 dark:hover:border-indigo-900 transition-all duration-300">
                        <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight leading-none mb-2">API ACCESS</h1>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)]"></span>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase tracking-widest">Developer Settings</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ asset('docs/api/API_REFERENCE.md') }}" target="_blank"
                        class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 hover:bg-indigo-600 hover:text-white transition-all duration-300 shadow-sm group">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-xs font-black uppercase tracking-widest">API Documentation</span>
                    </a>

                    <div class="px-5 py-3 rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-sm font-black text-gray-700 dark:text-gray-200">API v1.0.4 Online</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="relative z-10">
            <x-settings-nav />
        </div>

        <div class="relative">
            <!-- Decorative Background Elements -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl -z-10"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl -z-10"></div>
            
            <livewire:settings.api-token-manager />
        </div>
    </div>
@endsection

