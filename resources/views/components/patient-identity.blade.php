@props([
    'patient' => null,
    'subtitle' => null,
])

<div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-sm bg-violet-600">
        {{ substr(($patient?->first_name ?? 'U'), 0, 1) }}{{ substr(($patient?->last_name ?? 'N'), 0, 1) }}
    </div>
    <div class="min-w-0">
        <p class="font-bold text-gray-900 dark:text-white truncate">{{ $patient?->full_name ?? 'Unknown Patient' }}</p>
        <p class="text-[10px] text-violet-600 dark:text-violet-400 font-bold uppercase tracking-widest truncate">{{ $subtitle ?? ($patient?->uhid ?? 'N/A') }}</p>
    </div>
</div>
