<div class="space-y-8">
    <!-- Advanced Top Control Bar -->
    <div class="bg-slate-900 rounded-[2rem] p-6 shadow-2xl relative">
        <div class="absolute inset-0 overflow-hidden rounded-[2rem] pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-transparent"></div>
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        
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

                <!-- Searchable Village Dropdown -->
                <div x-data="{ open: false, search: '' }" @keydown.escape.window="open = false" class="relative min-w-[200px]">
                    <button @click.stop="open = !open" type="button" 
                            class="w-full flex items-center justify-between gap-2 bg-slate-800/50 border border-slate-700/50 text-slate-300 text-xs font-bold rounded-xl py-2.5 px-3 hover:bg-slate-800 transition-colors">
                        <span class="truncate">{{ $city ? $city : 'All Villages & Cities' }}</span>
                        <div class="flex items-center gap-1">
                            @if($city)
                                <span @click.stop="$wire.set('city', ''); open = false;" 
                                      class="text-slate-400 hover:text-rose-400 font-black text-sm px-1" title="Clear Village">×</span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>

                    <div x-show="open" @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-75" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95" 
                         class="absolute z-[100] top-full right-0 lg:left-0 mt-1.5 w-64 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden py-1" style="display: none;">
                        <div class="p-2 border-b border-slate-800">
                            <input type="text" x-model="search" @click.stop @keydown.escape="open = false" placeholder="Search village..." 
                                   class="w-full bg-slate-800 border border-slate-700 text-white text-xs font-semibold rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-800/40">
                            <button type="button" @click.stop="$wire.set('city', ''); open = false; search = '';" 
                                    class="w-full text-left px-3 py-2 text-xs font-bold hover:bg-indigo-600/20 text-slate-300 hover:text-white transition-colors">
                                All Villages & Cities
                            </button>
                            @foreach($villages as $v)
                                <button type="button" 
                                        x-show="!search || '{{ strtolower(addslashes($v)) }}'.includes(search.toLowerCase())" 
                                        @click.stop="$wire.set('city', '{{ addslashes($v) }}'); open = false; search = '';" 
                                        class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-indigo-600/20 text-slate-300 hover:text-white transition-colors truncate">
                                    {{ $v }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="h-8 w-px bg-slate-800 hidden sm:block"></div>

                <button wire:click="resetFilters" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-rose-400 text-xs font-bold rounded-xl px-3 py-2.5 transition-colors border border-slate-700/50" title="Clear All Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>

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
                <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Registrations</p>
                <div class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{{ number_format($stats['summary']['total_registrations']) }}</div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded-md">Total Patients</span>
                    <svg class="w-5 h-5 text-slate-500 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>

            <!-- Gender -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Gender Breakdown</p>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-end gap-2">
                    <span class="text-emerald-500" title="Male">{{ $stats['gender_distribution']['Male'] ?? 0 }} Male</span>
                    <span class="text-slate-500 dark:text-slate-300 font-light text-xl">/</span>
                    <span class="text-rose-500" title="Female">{{ $stats['gender_distribution']['Female'] ?? 0 }} Female</span>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-md">Breakdown</span>
                    <svg class="w-5 h-5 text-slate-500 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
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
                    <p class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Top Age Group</p>
                    <div class="text-xl font-black text-amber-500 tracking-tighter">{{ $topAgeGroup }}</div>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Top City/Village</p>
                    <div class="text-xl font-black text-blue-500 tracking-tighter truncate" title="{{ $topVillage }}">{{ $topVillage }}</div>
                </div>
            </div>
        </div>

        <!-- Main Charts Area -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Registration Trend Over Time</h3>
                </div>
                <div class="w-full">
                    <x-chart type="line" :data="$stats['daily_trend']" id="reg-trend-chart" label="Registrations" height="280px" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">City/Village Distribution</h3>
                    </div>
                    <div class="w-full">
                        <x-chart type="bar" :data="$stats['village_distribution']" id="village-dist-chart" label="Patients" height="240px" />
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Age Group Demographics</h3>
                    </div>
                    <div class="w-full">
                        <x-chart type="doughnut" :data="$stats['age_distribution']" id="age-dist-chart" label="Patients" height="240px" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Location Map Section -->
    <div x-data="googleMapHandler(@js($areaMapData))" x-init="initMap()" class="bg-slate-900 text-white rounded-[2.5rem] p-6 lg:p-8 border border-slate-800 shadow-2xl space-y-6 relative overflow-hidden">
        <!-- Ambient Glowing Background Blurs -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <!-- Top Header -->
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">
                        Patient Location Map
                    </h3>
                    @if($city)
                        <div class="flex items-center gap-2 bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-xl text-xs font-bold border border-emerald-500/30">
                            <span>City: <strong>{{ $city }}</strong></span>
                            <button wire:click="$set('city', '')" class="hover:text-white font-black text-sm ml-1" title="Clear City Filter">×</button>
                        </div>
                    @endif
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Patient locations and revenue distribution map</p>
            </div>

            <!-- View Switcher -->
            <div class="flex items-center p-1 bg-slate-950 rounded-2xl border border-slate-800">
                <button type="button" @click="activeTab = 'map'"
                        :class="activeTab === 'map' ? 'bg-emerald-500 text-slate-950 font-black shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white font-bold'"
                        class="px-4 py-2 text-xs uppercase rounded-xl transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Map View
                </button>
                <button type="button" @click="activeTab = 'embed'"
                        :class="activeTab === 'embed' ? 'bg-emerald-500 text-slate-950 font-black shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white font-bold'"
                        class="px-4 py-2 text-xs uppercase rounded-xl transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Satellite View
                </button>
                <button type="button" @click="activeTab = 'table'"
                        :class="activeTab === 'table' ? 'bg-emerald-500 text-slate-950 font-black shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white font-bold'"
                        class="px-4 py-2 text-xs uppercase rounded-xl transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    City List
                </button>
            </div>
        </div>

        <!-- Google Maps JS Container with Side Panel -->
        <div x-show="activeTab === 'map'" class="space-y-4 relative z-10">
            <!-- Heatmap Sub-Controls Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-950/90 p-3.5 rounded-2xl border border-slate-800 backdrop-blur-md">
                <!-- Layer Mode Switcher -->
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Map Style:</span>
                    <div class="flex items-center p-0.5 bg-slate-900 rounded-xl border border-slate-800">
                        <button type="button" @click="layerMode = 'hybrid'"
                                :class="layerMode === 'hybrid' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white font-bold'"
                                class="px-3 py-1 text-[10px] uppercase rounded-lg transition-all">
                            Both (Heatmap & Pins)
                        </button>
                        <button type="button" @click="layerMode = 'heatmap'"
                                :class="layerMode === 'heatmap' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white font-bold'"
                                class="px-3 py-1 text-[10px] uppercase rounded-lg transition-all">
                            Heatmap
                        </button>
                        <button type="button" @click="layerMode = 'markers'"
                                :class="layerMode === 'markers' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white font-bold'"
                                class="px-3 py-1 text-[10px] uppercase rounded-lg transition-all">
                            Count Pins
                        </button>
                    </div>
                </div>

                <!-- Weight Mode Switcher -->
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Focus On:</span>
                    <div class="flex items-center p-0.5 bg-slate-900 rounded-xl border border-slate-800">
                        <button type="button" @click="weightMode = 'count'"
                                :class="weightMode === 'count' ? 'bg-indigo-500 text-white font-black' : 'text-slate-400 hover:text-white font-bold'"
                                class="px-3 py-1 text-[10px] uppercase rounded-lg transition-all">
                            Patient Count
                        </button>
                        <button type="button" @click="weightMode = 'revenue'"
                                :class="weightMode === 'revenue' ? 'bg-indigo-500 text-white font-black' : 'text-slate-400 hover:text-white font-bold'"
                                class="px-3 py-1 text-[10px] uppercase rounded-lg transition-all">
                            Total Revenue (₹)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Grid: 8 Cols Map Canvas + 4 Cols Live Stream Panel -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full">
                <!-- Map Canvas -->
                <div class="lg:col-span-8 w-full">
                    <div wire:ignore class="relative w-full h-[520px] min-h-[520px] rounded-3xl overflow-hidden border border-slate-800 shadow-2xl" id="google-geo-map"></div>
                </div>

                <!-- Live Stream Panel -->
                <div class="lg:col-span-4 w-full bg-slate-950/90 rounded-3xl p-5 border border-slate-800 flex flex-col justify-between space-y-4 max-h-[520px] overflow-y-auto">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                            <h4 class="text-xs font-black text-white uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                Top Cities & Villages
                            </h4>
                            <span class="text-[10px] font-mono text-emerald-400 font-bold">{{ count($areaMapData) }} Cities</span>
                        </div>

                        <div class="space-y-3">
                            @forelse($areaMapData as $area)
                                <div @click="panToCity({{ $area['lat'] }}, {{ $area['lng'] }}, '{{ $area['name'] }}')"
                                     class="group cursor-pointer p-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/60 transition-all duration-300 relative overflow-hidden hover:scale-[1.01]">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-6 h-6 rounded-lg {{ $area['rank'] <= 2 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }} flex items-center justify-center text-[10px] font-black">
                                                #{{ $area['rank'] }}
                                            </span>
                                            <span class="text-xs font-black text-white group-hover:text-emerald-400 transition-colors">
                                                {{ $area['name'] }}
                                            </span>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-950 text-emerald-400 border border-emerald-800/60">
                                            {{ number_format($area['count']) }} Patients
                                        </span>
                                    </div>

                                    <!-- Progress Density Bar -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[10px] font-bold">
                                            <span class="text-slate-400">{{ $area['share'] }}% Share</span>
                                            <span class="text-emerald-400 font-mono">₹{{ number_format($area['revenue'], 0) }}</span>
                                        </div>
                                        <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, max(8, $area['share'] * 3)) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-500 text-xs font-semibold">
                                    No area data available.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                        Click any location to zoom on map & view patients
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps Satellite / Embed Iframe View -->
        <div x-show="activeTab === 'embed'" class="space-y-3 relative z-10">
            <div class="relative w-full h-[520px] rounded-3xl overflow-hidden border border-slate-800 shadow-xl">
                @php
                    $primaryCity = count($areaMapData) > 0 ? $areaMapData[0]['name'] : 'Nizamabad,Telangana';
                @endphp
                <iframe class="w-full h-full border-0"
                        loading="lazy"
                        allowfullscreen
                        src="https://maps.google.com/maps?q={{ urlencode($primaryCity) }}&t=&z=10&ie=UTF8&iwloc=&output=embed">
                </iframe>
            </div>
            <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">
                <span>Google Maps Satellite View</span>
                <span>Centered on {{ $primaryCity }}</span>
            </div>
        </div>

        <!-- Area Data Table View -->
        <div x-show="activeTab === 'table'" class="overflow-x-auto relative z-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950 border-b border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Rank & Village / Region</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Patients Registered</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Market Share (%)</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Gross Revenue (₹)</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Avg Spend / Patient</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($areaMapData as $area)
                    <tr class="hover:bg-slate-950/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs font-black text-emerald-400">#{{ $area['rank'] }}</span>
                                <button wire:click="$set('city', '{{ $area['name'] }}')" class="text-xs font-black text-white hover:text-emerald-400">
                                    {{ $area['name'] }}
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-center text-white">
                            {{ number_format($area['count']) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-center text-emerald-400">
                            {{ $area['share'] }}%
                        </td>
                        <td class="px-6 py-4 text-xs font-black text-right text-emerald-400 font-mono">
                            ₹{{ number_format($area['revenue'], 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-right text-slate-300 font-mono">
                            ₹{{ number_format($area['avg_spend'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm font-semibold text-slate-500">No geographic area data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function googleMapHandler(locations) {
        return {
            activeTab: 'map',
            map: null,
            heatmapLayer: null,
            markers: [],
            locations: locations,
            weightMode: 'count', // 'count' or 'revenue'
            layerMode: 'hybrid', // 'heatmap', 'markers', 'hybrid'
            isLeaflet: false,

            panToCity(lat, lng, cityName) {
                if (this.map) {
                    if (this.isLeaflet && this.map.setView) {
                        this.map.setView([lat, lng], 12);
                    } else if (this.map.panTo) {
                        this.map.panTo({ lat: lat, lng: lng });
                        this.map.setZoom(12);
                    }
                }
                this.$wire.set('city', cityName);
            },

            initMap() {
                window.addEventListener('update-map-data', (e) => {
                    if (e.detail && e.detail.areaMapData) {
                        this.locations = e.detail.areaMapData;
                        this.updateLayers();
                    }
                });

                this.$watch('activeTab', (val) => {
                    if (val === 'map') {
                        setTimeout(() => {
                            if (this.isLeaflet && this.map && this.map.invalidateSize) {
                                this.map.invalidateSize();
                            } else if (this.map && window.google && window.google.maps) {
                                google.maps.event.trigger(this.map, 'resize');
                            }
                        }, 250);
                    }
                });

                this.$watch('weightMode', () => this.updateLayers());
                this.$watch('layerMode', () => this.updateLayers());

                const renderMap = () => {
                    const mapContainer = document.getElementById('google-geo-map');
                    if (!mapContainer) return;

                    const centerLat = 18.6725; // Nizamabad District Center
                    const centerLng = 78.0941;

                    // Google Maps Attempt
                    if (window.google && window.google.maps && !window.googleMapsFailed) {
                        try {
                            this.isLeaflet = false;
                            this.map = new google.maps.Map(mapContainer, {
                                center: { lat: centerLat, lng: centerLng },
                                zoom: 11,
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                mapTypeControl: false,
                                streetViewControl: false,
                                fullscreenControl: true,
                                styles: [
                                    { elementType: "geometry", stylers: [{ color: "#060913" }] },
                                    { elementType: "labels.text.stroke", stylers: [{ color: "#060913" }] },
                                    { elementType: "labels.text.fill", stylers: [{ color: "#94a3b8" }] },
                                    { featureType: "administrative.locality", elementType: "labels.text.fill", stylers: [{ color: "#38bdf8" }] },
                                    { featureType: "road", elementType: "geometry", stylers: [{ color: "#1e293b" }] },
                                    { featureType: "road.highway", elementType: "geometry", stylers: [{ color: "#334155" }] },
                                    { featureType: "water", elementType: "geometry", stylers: [{ color: "#020617" }] }
                                ]
                            });
                            this.updateLayers();
                            return;
                        } catch (e) {
                            console.warn('Google Maps init failed, switching to Leaflet:', e);
                        }
                    }

                    // Leaflet OpenStreetMap Fallback
                    if (typeof L !== 'undefined') {
                        this.isLeaflet = true;
                        if (mapContainer._leaflet_id) {
                            mapContainer._leaflet_id = null;
                            mapContainer.innerHTML = '';
                        }

                        this.map = L.map('google-geo-map', {
                            center: [centerLat, centerLng],
                            zoom: 11,
                            zoomControl: true,
                        });

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                            maxZoom: 19,
                            attribution: '&copy; CartoDB & OpenStreetMap'
                        }).addTo(this.map);

                        this.updateLayers();
                    }
                };

                window.addEventListener('google-maps-failed', () => renderMap());

                if (window.google && window.google.maps && !window.googleMapsFailed) {
                    renderMap();
                } else if (typeof L !== 'undefined') {
                    renderMap();
                } else {
                    window.addEventListener('google-maps-loaded', renderMap);
                    setTimeout(renderMap, 500);
                }
            },

            updateLayers() {
                if (!this.map) return;

                if (this.heatmapLayer && this.heatmapLayer.setMap) {
                    this.heatmapLayer.setMap(null);
                    this.heatmapLayer = null;
                }

                if (this.markers) {
                    this.markers.forEach(m => {
                        if (m.setMap) m.setMap(null);
                        else if (m.remove) m.remove();
                    });
                    this.markers = [];
                }

                if (!Array.isArray(this.locations) || this.locations.length === 0) return;

                if (this.isLeaflet) {
                    const bounds = [];
                    this.locations.forEach(area => {
                        const color = area.rank <= 3 ? '#06b6d4' : (area.rank <= 8 ? '#6366f1' : '#f59e0b');

                        if (this.layerMode === 'heatmap' || this.layerMode === 'hybrid') {
                            const radius = Math.max(18, Math.min(50, area.count * 2.5));
                            L.circleMarker([area.lat, area.lng], {
                                radius: radius,
                                color: color,
                                fillColor: color,
                                fillOpacity: 0.45,
                                weight: 2.5
                            }).addTo(this.map);
                        }

                        if (this.layerMode === 'markers' || this.layerMode === 'hybrid') {
                            const displayVal = this.weightMode === 'count' 
                                ? `${area.count}` 
                                : `₹${(area.revenue/1000).toFixed(0)}k`;

                            const countBadgeHtml = `
                                <div style="background: linear-gradient(135deg, ${color}, #090d16); color: #ffffff; min-width: 36px; height: 36px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 11px; border: 2.5px solid #ffffff; box-shadow: 0 4px 16px rgba(0,0,0,0.6); font-family: sans-serif; text-align: center; padding: 0 6px;">
                                    ${displayVal}
                                </div>
                            `;

                            const customIcon = L.divIcon({
                                html: countBadgeHtml,
                                className: 'custom-leaflet-count-badge',
                                iconSize: [36, 36],
                                iconAnchor: [18, 18]
                            });

                            const marker = L.marker([area.lat, area.lng], { icon: customIcon }).addTo(this.map);
                            
                            const popupHtml = `
                                <div style="font-family: sans-serif; padding: 6px; min-width: 150px; color: #0f172a;">
                                    <h4 style="font-weight: 900; font-size: 13px; margin: 0 0 4px 0;">${area.name}</h4>
                                    <div style="font-size: 10px; color: #64748b; margin-bottom: 6px;">Rank #${area.rank} • ${area.share}% Market Share</div>
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold; margin-bottom: 4px;">
                                        <span>Patients:</span>
                                        <span style="color: #4f46e5;">${area.count}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold;">
                                        <span>Revenue:</span>
                                        <span style="color: #059669;">₹${new Intl.NumberFormat('en-IN').format(area.revenue)}</span>
                                    </div>
                                </div>
                            `;
                            marker.bindPopup(popupHtml);
                            this.markers.push(marker);
                        }

                        bounds.push([area.lat, area.lng]);
                    });

                    if (bounds.length > 1) {
                        this.map.fitBounds(bounds, { padding: [40, 40] });
                    }
                } else if (window.google && window.google.maps) {
                    const bounds = new google.maps.LatLngBounds();

                    if ((this.layerMode === 'heatmap' || this.layerMode === 'hybrid') && google.maps.visualization && google.maps.visualization.HeatmapLayer) {
                        const heatmapPoints = this.locations.map(loc => {
                            const weightVal = this.weightMode === 'count' ? loc.count : Math.max(1, loc.revenue / 500);
                            return {
                                location: new google.maps.LatLng(loc.lat, loc.lng),
                                weight: Math.max(1, weightVal)
                            };
                        });

                        this.heatmapLayer = new google.maps.visualization.HeatmapLayer({
                            data: heatmapPoints,
                            map: this.map,
                            radius: 50,
                            opacity: 0.9,
                            gradient: [
                                'rgba(0, 242, 254, 0)',
                                'rgba(6, 182, 212, 0.5)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.9)',
                                'rgba(249, 115, 22, 0.95)',
                                'rgba(225, 29, 72, 1)'
                            ]
                        });
                    }

                    if (this.layerMode === 'markers' || this.layerMode === 'hybrid') {
                        this.locations.forEach(area => {
                            const position = { lat: area.lat, lng: area.lng };
                            bounds.extend(position);

                            const displayVal = this.weightMode === 'count' 
                                ? `${area.count}` 
                                : `₹${(area.revenue/1000).toFixed(0)}k`;

                            const color = area.rank <= 3 ? '#06b6d4' : (area.rank <= 8 ? '#6366f1' : '#f59e0b');

                            const marker = new google.maps.Marker({
                                position: position,
                                map: this.map,
                                title: `${area.name}: ${area.count} Patients (₹${area.revenue})`,
                                label: {
                                    text: displayVal,
                                    color: '#ffffff',
                                    fontSize: '11px',
                                    fontWeight: '900'
                                },
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: Math.max(18, Math.min(34, area.count * 1.6)),
                                    fillColor: color,
                                    fillOpacity: 0.95,
                                    strokeWeight: 3,
                                    strokeColor: '#ffffff'
                                }
                            });

                            const infoWindow = new google.maps.InfoWindow({
                                content: `
                                    <div style="font-family: sans-serif; padding: 6px; min-width: 160px; color: #0f172a;">
                                        <h4 style="font-weight: 900; font-size: 14px; margin: 0 0 4px 0; color: #1e293b;">${area.name}</h4>
                                        <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">Rank #${area.rank} • ${area.share}% Market Share</div>
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold; margin-bottom: 4px;">
                                            <span>Registered Patients:</span>
                                            <span style="color: #4f46e5;">${area.count}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold;">
                                            <span>Gross Revenue:</span>
                                            <span style="color: #059669;">₹${new Intl.NumberFormat('en-IN').format(area.revenue)}</span>
                                        </div>
                                    </div>
                                `
                            });

                            marker.addListener('click', () => {
                                infoWindow.open(this.map, marker);
                                this.$wire.set('city', area.name);
                            });

                            this.markers.push(marker);
                        });

                        if (this.locations.length > 1) {
                            this.map.fitBounds(bounds);
                        }
                    }
                }
            }
        }
    }
    </script>

    <!-- Advanced Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden relative">
        <div wire:loading class="absolute inset-0 z-50 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm flex items-center justify-center">
            <div class="flex items-center gap-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700">
                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-500 dark:text-slate-300 uppercase tracking-wider">Crunching Data...</span>
            </div>
        </div>

        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50 bg-slate-50/30 dark:bg-slate-800/20">
            <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Extracted Records <span class="text-indigo-500">({{ $patients->total() }})</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap">Timestamp</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Patient Identity</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Demographics</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Location</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Contact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse($patients as $patient)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $patient->created_at->format('M d, Y') }}</div>
                            <div class="text-[11px] font-medium text-slate-600 dark:text-slate-400 font-mono mt-0.5">{{ $patient->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-400">
                                    {{ substr($patient->first_name ?? 'U', 0, 1) }}{{ substr($patient->last_name ?? '', 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('counter.patients.history', $patient->id) }}" class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ $patient->full_name }}
                                    </a>
                                    <div class="text-[11px] font-medium text-indigo-500 font-mono mt-0.5">{{ $patient->uhid }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold rounded-md {{ $patient->gender === 'Male' ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' : ($patient->gender === 'Female' ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-500 dark:text-slate-400') }}">
                                    {{ substr($patient->gender ?? 'U', 0, 1) }}
                                </span>
                                <span class="text-sm font-semibold text-slate-600 dark:text-slate-500 dark:text-slate-300">{{ $patient->age }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-500 dark:text-slate-300 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ \App\Livewire\Reports\RegistrationReport::extractVillage($patient->city, $patient->address) }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-600 dark:text-slate-500 dark:text-slate-400 font-mono">
                                {{ $patient->phone ?? 'N/A' }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 mb-4">
                                <svg class="w-8 h-8 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">No Records Found</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Try adjusting your filters or search query.</p>
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
