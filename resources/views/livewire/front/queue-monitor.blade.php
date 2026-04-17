@php
    $singleDoctor = count($stats) === 1;
@endphp

<div wire:poll.5s class="space-y-8">
    <div class="grid grid-cols-1 {{ count($stats) > 1 ? 'md:grid-cols-2 lg:grid-cols-3' : 'max-w-4xl mx-auto' }} gap-8">
        @foreach($stats as $stat)
            <div wire:key="queue-doctor-{{ data_get($stat, 'doctor.id', 'na') }}" class="bg-white dark:bg-gray-800 rounded-[3.5rem] border border-gray-100 dark:border-gray-700/50 shadow-2xl overflow-hidden group hover:scale-[1.01] transition-transform">
                @if(!$singleDoctor)
                    <!-- Doctor Header (Only if multiple) -->
                    <div class="p-6 bg-indigo-600 text-white relative">
                        <div class="relative z-10">
                            <h2 class="text-xl font-black uppercase tracking-tight">DR. {{ data_get($stat, 'doctor.full_name', 'Unassigned') }}</h2>
                            <p class="text-tiny font-black text-indigo-200 uppercase tracking-widest mt-0.5">{{ data_get($stat, 'doctor.department.name', 'No Department') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Large Token Display -->
                <div class="p-12 text-center bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-[12px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.4em] mb-4 block">Current Patient</span>
                    <div class="flex flex-col items-center justify-center">
                        <span class="text-9xl font-black text-gray-900 dark:text-gray-100 tracking-tighter italic">#{{ str_pad($stat['ongoing_token'], 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-tight mt-6">{{ $stat['ongoing_patient'] }}</h3>
                    </div>
                </div>

                <!-- Footer Stats -->
                <div class="p-8 grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-800">
                    <div class="text-center">
                        <span class="text-tiny font-black text-amber-500 uppercase tracking-widest block mb-1">Up Next</span>
                        <span class="text-4xl font-black text-gray-400 italic">#{{ str_pad($stat['next_token'], 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="text-center">
                        <span class="text-tiny font-black text-emerald-500 uppercase tracking-widest block mb-1">Status</span>
                        <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest">In Progress</span>
                    </div>
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
