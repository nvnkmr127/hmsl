<div class="glass-card overflow-hidden">
    <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50">
        <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.2em]">Front Desk Staff</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($receptionists as $staff)
            <div class="p-4 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/10 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 font-bold shadow-inner">
                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $staff->name }}</p>
                    <p class="text-tiny font-medium text-gray-400 uppercase tracking-widest">{{ $staff->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-tiny font-bold text-gray-400 uppercase">Available</span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-gray-400 font-medium">No receptionists found.</p>
            </div>
        @endforelse
    </div>
</div>
