<div class="space-y-8">
    <!-- Advanced Top Control Bar -->
    <div class="bg-slate-900 rounded-[2rem] p-6 shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-transparent"></div>
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl font-black text-white uppercase tracking-tight">Registration Matrix</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-6 h-1 rounded-full bg-indigo-500"></span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Real-time analytics engine</p>
                </div>
            </div>

            <!-- Filters Container -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Box -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search patients..." class="bg-slate-800/50 border border-slate-700/50 text-white text-xs font-semibold rounded-xl pl-9 pr-4 py-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-64 placeholder-slate-500 transition-all">
                </div>

                <div class="h-8 w-px bg-slate-800 hidden sm:block"></div>

                <!-- Date Range -->
                <div class="flex items-center gap-2 bg-slate-800/50 p-1 rounded-xl border border-slate-700/50">
                    <input type="date" wire:model.live="from" class="bg-transparent border-none text-xs font-bold text-slate-300 focus:ring-0 cursor-pointer py-1.5 px-2">
                    <span class="text-slate-600">→</span>
                    <input type="date" wire:model.live="to" class="bg-transparent border-none text-xs font-bold text-slate-300 focus:ring-0 cursor-pointer py-1.5 px-2">
                </div>

                <!-- Dropdowns -->
                <select wire:model.live="gender" class="bg-slate-800/50 border border-slate-700/50 text-slate-300 text-xs font-bold rounded-xl focus:ring-1 focus:ring-indigo-500 cursor-pointer py-2 px-3">
                    <option value="">All Genders</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>

                <select wire:model.live="ageGroup" class="bg-slate-800/50 border border-slate-700/50 text-slate-300 text-xs font-bold rounded-xl focus:ring-1 focus:ring-indigo-500 cursor-pointer py-2 px-3">
                    <option value="">All Ages</option>
                    <option value="0-1 Year">0-1 Year</option>
                    <option value="1-5 Years">1-5 Years</option>
                    <option value="5-12 Years">5-12 Years</option>
                    <option value="12+ Years">12+ Years</option>
                </select>

                <select wire:model.live="city" class="bg-slate-800/50 border border-slate-700/50 text-slate-300 text-xs font-bold rounded-xl focus:ring-1 focus:ring-indigo-500 cursor-pointer py-2 px-3 max-w-[120px]">
                    <option value="">All Villages</option>
                    @foreach($villages as $v)
                        <option value="{{ $v }}">{{ $v }}</option>
                    @endforeach
                </select>

                <div class="h-8 w-px bg-slate-800 hidden sm:block"></div>

                <!-- Export Action -->
                <button wire:click="exportCSV" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black uppercase tracking-wider rounded-xl px-4 py-2.5 transition-colors shadow-lg shadow-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Key Metrics Column -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-6">
            <!-- Total -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Flow</p>
                <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($stats['summary']['total_registrations']) }}</div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded-md">Registrations</span>
                    <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>

            <!-- Gender -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gender Dist.</p>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-end gap-2">
                    <span class="text-emerald-500" title="Male">{{ $stats['gender_distribution']['Male'] ?? 0 }} M</span>
                    <span class="text-slate-300 font-light text-xl">/</span>
                    <span class="text-rose-500" title="Female">{{ $stats['gender_distribution']['Female'] ?? 0 }} F</span>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-md">Breakdown</span>
                    <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            
            @php
                $topAgeGroup = '--';
                $maxCount = 0;
                foreach($stats['age_distribution'] as $group => $count) {
                    if($count > $maxCount) { $maxCount = $count; $topAgeGroup = $group; }
                }
                
                $topVillage = '--';
                if (!empty($stats['village_distribution'])) {
                    $topVillage = array_key_first($stats['village_distribution']);
                }
            @endphp

            <!-- Dominants -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm col-span-1 sm:col-span-2 lg:col-span-1 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Top Age</p>
                    <div class="text-xl font-black text-amber-500 tracking-tighter">{{ $topAgeGroup }}</div>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Top Village</p>
                    <div class="text-xl font-black text-blue-500 tracking-tighter truncate" title="{{ $topVillage }}">{{ $topVillage }}</div>
                </div>
            </div>
        </div>

        <!-- Main Charts Area -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Registration Trend</h3>
                </div>
                <div class="w-full">
                    <x-chart type="line" :data="$stats['daily_trend']" id="reg-trend-chart" label="Registrations" height="280px" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Village Influx</h3>
                    </div>
                    <div class="w-full">
                        <x-chart type="bar" :data="$stats['village_distribution']" id="village-dist-chart" label="Patients" height="240px" />
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Age Demographics</h3>
                    </div>
                    <div class="w-full">
                        <x-chart type="doughnut" :data="$stats['age_distribution']" id="age-dist-chart" label="Patients" height="240px" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden relative">
        <div wire:loading class="absolute inset-0 z-50 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm flex items-center justify-center">
            <div class="flex items-center gap-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700">
                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Crunching Data...</span>
            </div>
        </div>

        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50 bg-slate-50/30 dark:bg-slate-800/20">
            <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Extracted Records <span class="text-indigo-500">({{ $patients->total() }})</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Timestamp</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient Identity</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Demographics</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Location</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse($patients as $patient)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $patient->created_at->format('M d, Y') }}</div>
                            <div class="text-[11px] font-medium text-slate-500 font-mono mt-0.5">{{ $patient->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-500">
                                    {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('patients.show', $patient->id) }}" class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ $patient->full_name }}
                                    </a>
                                    <div class="text-[11px] font-medium text-indigo-500 font-mono mt-0.5">{{ $patient->uhid }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold rounded-md {{ $patient->gender === 'Male' ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' : ($patient->gender === 'Female' ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400') }}">
                                    {{ substr($patient->gender ?? 'U', 0, 1) }}
                                </span>
                                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $patient->age }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $patient->city ?? '--' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-600 dark:text-slate-400 font-mono">
                                {{ $patient->phone ?? 'N/A' }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">No Records Found</h3>
                            <p class="text-xs text-slate-500 mt-1">Try adjusting your filters or search query.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
        <div class="p-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            {{ $patients->links() }}
        </div>
        @endif
    </div>
</div>
