<div class="space-y-10">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">OPD Visit Intelligence</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mt-2">Analyzing clinical volume and revisit patterns</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <input type="date" wire:model.live="from" class="bg-transparent border-none text-xs font-black uppercase text-slate-600 dark:text-slate-300 focus:ring-0 cursor-pointer">
                    <span class="text-slate-300">→</span>
                    <input type="date" wire:model.live="to" class="bg-transparent border-none text-xs font-black uppercase text-slate-600 dark:text-slate-300 focus:ring-0 cursor-pointer">
                </div>

                <select wire:model.live="doctorId" class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary-500/20 transition-all cursor-pointer">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="departmentId" class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary-500/20 transition-all cursor-pointer">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm group hover:border-primary-500/30 transition-all duration-500">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Visits</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($stats['summary']['total_visits']) }}</div>
            <div class="mt-2 text-[10px] font-bold text-emerald-500 uppercase">Growth Analysis Active</div>
        </div>

        @foreach($stats['summary']['visit_types'] as $type => $count)
        <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center text-slate-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $type }} Visits</span>
            </div>
            <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($count) }}</div>
            <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase">{{ round(($count / max(1, $stats['summary']['total_visits'])) * 100) }}% of Total</div>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Daily Trend -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Visit Velocity</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Daily patient flow trend</p>
                </div>
            </div>
            <x-chart type="line" :data="$stats['daily_trend']" id="visit-trend-chart" label="Visits" />
        </div>

        <!-- Doctor Breakdown -->
        <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Consultation Share</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Visit distribution by physician</p>
                </div>
            </div>
            <x-chart type="bar" :data="$stats['doctor_wise']" id="doctor-share-chart" label="Patients Seen" />
        </div>
    </div>

    <!-- Patient Visits Datatable -->
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-10 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Patient Visit Records</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Detailed list of all consultations</p>
            </div>
            <button class="px-6 py-2.5 bg-slate-900 dark:bg-primary-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:scale-105 transition-all">Export CSV</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date & Time</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Doctor</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Department</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Visit Type</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse($visits as $visit)
                    <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($visit->consultation_date)->format('d M Y') }}</div>
                            <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($visit->consultation_time)->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('patients.show', $visit->patient_id) }}" class="text-sm font-black text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                {{ $visit->patient->full_name ?? 'N/A' }}
                            </a>
                            <div class="text-xs text-slate-500">{{ $visit->patient->uhid ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $visit->doctor->full_name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-400">
                            {{ $visit->doctor->department->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $visit->visit_type == 'New' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400' }}">
                                {{ $visit->visit_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $visit->status == 'Completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' }}">
                                {{ $visit->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-slate-500">No visits found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-slate-50 dark:border-slate-800">
            {{ $visits->links() }}
        </div>
    </div>
</div>
