<div class="space-y-10">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">IPD Visit Intelligence</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mt-2">Analyzing inpatient volume and bed occupancy</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <input type="date" wire:model.live="from" class="bg-transparent border-none text-xs font-black uppercase text-slate-600 dark:text-slate-300 focus:ring-0 cursor-pointer">
                    <span class="text-slate-300">→</span>
                    <input type="date" wire:model.live="to" class="bg-transparent border-none text-xs font-black uppercase text-slate-600 dark:text-slate-300 focus:ring-0 cursor-pointer">
                </div>

                <select wire:model.live="doctorId" class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="wardId" class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer">
                    <option value="">All Wards</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm group hover:border-indigo-500/30 transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Admissions</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($stats['summary']['total_admissions']) }}</div>
            <div class="mt-2 text-[10px] font-bold text-indigo-500 uppercase">Within Date Range</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm group hover:border-emerald-500/30 transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Active Admissions</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($stats['summary']['active_admissions']) }}</div>
            <div class="mt-2 text-[10px] font-bold text-emerald-500 uppercase">Currently Admitted</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm group hover:border-amber-500/30 transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Discharges</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($stats['summary']['discharges']) }}</div>
            <div class="mt-2 text-[10px] font-bold text-amber-500 uppercase">Within Date Range</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Daily Trend -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Admission Velocity</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Daily inpatient flow trend</p>
                </div>
            </div>
            <x-chart type="bar" :data="$stats['daily_trend']" id="admission-trend-chart" label="Admissions" />
        </div>

        <!-- Ward Breakdown -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Ward Distribution</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Admissions by ward</p>
                </div>
            </div>
            <x-chart type="doughnut" :data="$stats['ward_distribution']" id="ward-share-chart" label="Admissions" />
        </div>
    </div>

    <!-- Patient Admissions Datatable -->
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-10 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Inpatient Records</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Detailed list of all admissions</p>
            </div>
            <button class="px-6 py-2.5 bg-slate-900 dark:bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:scale-105 transition-all">Export CSV</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date & Time</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Doctor</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Ward / Bed</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse($admissions as $admission)
                    <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') }}</div>
                            <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($admission->admission_time)->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('patients.show', $admission->patient_id) }}" class="text-sm font-black text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                {{ $admission->patient->full_name ?? 'N/A' }}
                            </a>
                            <div class="text-xs text-slate-500">{{ $admission->patient->uhid ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $admission->doctor->full_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-400">
                            {{ $admission->bed->ward->name ?? 'N/A' }} <br>
                            <span class="text-xs text-slate-400">Bed: {{ $admission->bed->bed_number ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $admission->status == 'Admitted' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' }}">
                                {{ $admission->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm font-semibold text-slate-500">No admissions found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-slate-50 dark:border-slate-800">
            {{ $admissions->links() }}
        </div>
    </div>
</div>
