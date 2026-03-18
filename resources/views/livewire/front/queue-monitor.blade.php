<div wire:poll.5s class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($stats as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-xl overflow-hidden group hover:scale-[1.02] transition-transform">
                <!-- Doctor Header -->
                <div class="p-8 bg-indigo-600 text-white relative">
                    <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-10">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <h2 class="text-2xl font-black uppercase tracking-tight">DR. {{ data_get($stat, 'doctor.full_name', 'Unassigned') }}</h2>
                        <p class="text-xs font-black text-indigo-200 uppercase tracking-widest mt-1">{{ data_get($stat, 'doctor.department.name', 'No Department') }}</p>
                    </div>
                </div>

                <!-- Status Grid -->
                <div class="p-8 grid grid-cols-2 gap-8 bg-gray-50/50 dark:bg-gray-900/40">
                    <div class="space-y-2">
                        <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Ongoing</span>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-4xl font-black text-gray-800 dark:text-gray-100 italic">#{{ str_pad($stat['ongoing_token'], 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-right">
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Next Token</span>
                        <div class="flex items-baseline justify-end space-x-2">
                            <span class="text-4xl font-black text-gray-400 italic">#{{ str_pad($stat['next_token'], 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Patient Footer -->
                <div class="px-8 py-6 flex items-center justify-between border-t border-gray-100 dark:border-gray-700/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-tight">{{ $stat['ongoing_patient'] }}</span>
                    </div>
                    <x-badge color="sky">In Consultation</x-badge>
                </div>
            </div>
        @endforeach
    </div>

    @if(empty($stats))
        <div class="text-center py-20 bg-gray-50 dark:bg-gray-800 rounded-[3rem] border-4 border-dashed border-gray-100 dark:border-gray-700">
            <h2 class="text-2xl font-black text-gray-300 uppercase tracking-tighter">No Active Consultations Today</h2>
        </div>
    @endif
</div>
