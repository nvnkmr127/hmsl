<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    {{-- Total Appointments --}}
    <div class="glass-card p-6 bg-gradient-to-br from-indigo-500/10 to-transparent border border-indigo-100 dark:border-indigo-900/30">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <span class="text-tiny font-black text-indigo-500 uppercase tracking-widest px-2 py-1 bg-indigo-50 dark:bg-indigo-950/30 rounded-lg">Lifetime</span>
        </div>
        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Total Appointments</p>
        <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($stats['total_appointments']) }}</h3>
    </div>

    {{-- Monthly Earnings --}}
    <div class="glass-card p-6 bg-gradient-to-br from-emerald-500/10 to-transparent border border-emerald-100 dark:border-emerald-900/30">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-tiny font-black text-emerald-500 uppercase tracking-widest px-2 py-1 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg">{{ now()->format('F') }}</span>
        </div>
        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Monthly Earnings</p>
        <h3 class="text-3xl font-black text-gray-900 dark:text-white">₹{{ number_format($stats['monthly_earnings'], 2) }}</h3>
    </div>

    {{-- Today's Pending --}}
    <div class="glass-card p-6 bg-gradient-to-br from-amber-500/10 to-transparent border border-amber-100 dark:border-amber-900/30">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-tiny font-black text-amber-500 uppercase tracking-widest px-2 py-1 bg-amber-50 dark:bg-amber-950/30 rounded-lg">Today</span>
        </div>
        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Pending Queue</p>
        <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ $stats['pending_today'] }}</h3>
    </div>

    {{-- Today's Completed --}}
    <div class="glass-card p-6 bg-gradient-to-br from-violet-500/10 to-transparent border border-violet-100 dark:border-violet-900/30">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center text-violet-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-tiny font-black text-violet-500 uppercase tracking-widest px-2 py-1 bg-violet-50 dark:bg-violet-950/30 rounded-lg">Today</span>
        </div>
        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Completed</p>
        <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ $stats['completed_today'] }}</h3>
    </div>

    {{-- Active IPD Patients --}}
    <div class="glass-card p-6 bg-gradient-to-br from-rose-500/10 to-transparent border border-rose-100 dark:border-rose-900/30">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <span class="text-tiny font-black text-rose-500 uppercase tracking-widest px-2 py-1 bg-rose-50 dark:bg-rose-950/30 rounded-lg">In-Patient</span>
        </div>
        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Active IPD</p>
        <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ $stats['active_ipd'] }}</h3>
    </div>

    {{-- Total Admissions --}}
    <div class="glass-card p-6 bg-gradient-to-br from-blue-500/10 to-transparent border border-blue-100 dark:border-blue-900/30">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <span class="text-tiny font-black text-blue-500 uppercase tracking-widest px-2 py-1 bg-blue-50 dark:bg-blue-950/30 rounded-lg">Lifetime</span>
        </div>
        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Total Admissions</p>
        <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($stats['total_admissions']) }}</h3>
    </div>
</div>
