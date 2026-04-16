<div class="relative flex-1 max-w-sm lg:max-w-md" x-data="{ isOpen: @entangle('isOpen') }" @click.away="isOpen = false">
    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-2xl px-4 py-2.5 group border border-transparent focus-within:border-violet/20 focus-within:bg-white dark:focus-within:bg-gray-800 transition-all">
        <svg class="w-4 h-4 text-gray-400 group-focus-within:text-violet transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input 
            wire:model.live.debounce.300ms="query" 
            type="text" 
            placeholder="Search patients, doctors or tokens..." 
            class="bg-transparent text-sm font-bold text-gray-700 dark:text-gray-200 placeholder-gray-400 outline-none flex-1 min-w-0"
            @focus="isOpen = true"
        >
        <div wire:loading class="w-4 h-4 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Search Results Dropdown -->
    <div 
        x-show="isOpen && query.length >= 2" 
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="absolute left-0 right-0 mt-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-2xl overflow-hidden z-[60]"
    >
        @if(empty($results['patients']) && empty($results['doctors']) && empty($results['consultations']))
            <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                No results found for "{{ $query }}"
            </div>
        @else
            <div class="max-h-96 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800">
                
                @if(!empty($results['patients']))
                    <div class="p-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-3 py-2">Patients</p>
                        @foreach($results['patients'] as $patient)
                            <a href="{{ route('counter.patients.show', $patient->id) }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600">{{ $patient->full_name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $patient->uhid }} • {{ $patient->phone }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if(!empty($results['doctors']))
                    <div class="p-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-3 py-2">Doctors</p>
                        @foreach($results['doctors'] as $doctor)
                            <a href="{{ route('master.doctors.index') }}?search={{ $doctor->full_name }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600">Dr. {{ $doctor->full_name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $doctor->specialization }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if(!empty($results['consultations']))
                    <div class="p-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-3 py-2">Active Tokens</p>
                        @foreach($results['consultations'] as $con)
                            <a href="{{ route('counter.opd.index') }}?search={{ $con->token_number }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 text-xs font-black">
                                    #{{ $con->token_number }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600">{{ $con->patient->full_name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $con->consultation_date->format('d M') }} • {{ $con->status }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        @endif
    </div>
</div>
