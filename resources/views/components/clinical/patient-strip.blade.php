@props([
    'patient',
    'size'    => 'md',
    'active'  => false,
    'showActions' => false,
])

@php
    $initial = strtoupper(substr($patient->first_name ?? '?', 0, 1));
    $classes = $active ? 'bg-indigo-600 text-white' : 'bg-indigo-50/50 dark:bg-indigo-950/20 border-2 border-indigo-100/50 dark:border-indigo-900/30 shadow-inner';
    $rounded = $size === 'lg' ? 'rounded-mega' : 'rounded-2xl';
@endphp

<div {{ $attributes->merge(['class' => "flex items-center gap-4 p-4 $rounded $classes transition-all duration-500 group"]) }}>
    <div @class([
        'rounded-2xl flex items-center justify-center font-black transition-all duration-500',
        'w-10 h-10 text-sm' => $size === 'sm',
        'w-12 h-12 text-lg shadow-lg' => $size === 'md',
        'w-16 h-16 text-2xl shadow-xl' => $size === 'lg',
        'bg-indigo-500 text-white shadow-indigo-500/40' => !$active,
        'bg-white text-indigo-600' => $active,
    ])>
        {{ $initial }}
    </div>
    
    <div class="flex flex-col flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span @class([
                'font-black uppercase tracking-tight truncate transition-colors',
                'text-sm' => $size === 'sm',
                'text-base' => $size === 'md',
                'text-xl' => $size === 'lg',
                'text-white' => $active,
                'text-gray-900 dark:text-white group-hover:text-indigo-500' => !$active,
            ])>
                {{ $patient->full_name }}
            </span>
            @if($size !== 'sm')
                <x-badge color="{{ $active ? 'white' : 'indigo' }}" size="sm">{{ $patient->uhid }}</x-badge>
            @endif
        </div>
        
        <div @class([
            'flex items-center gap-2 mt-0.5 font-bold uppercase tracking-widest',
            'text-dense' => $size === 'sm',
            'text-tiny' => $size === 'md' || $size === 'lg',
            'text-indigo-200' => $active,
            'text-gray-400 dark:text-gray-500' => !$active,
        ])>
            @if($size === 'sm')
                <span class="text-indigo-300">{{ $patient->uhid }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
            @endif
            <span>{{ $patient->age }} · {{ $patient->gender }}</span>
            <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700 opacity-30"></span>
            <span @class(['text-emerald-500' => !$active, 'text-white' => $active])>{{ $patient->phone }}</span>
        </div>
    </div> 

    @if($showActions)
        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">
            <svg class="w-4 h-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
        </div>
    @endif
</div>
