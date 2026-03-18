@props(['sortable' => false, 'active' => false, 'direction' => 'asc'])

<th {{ $attributes->merge(['class' => 'whitespace-nowrap ' . ($sortable ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors' : '')]) }}>
    <div class="flex items-center gap-1.5">
        <span>{{ $slot }}</span>
        @if($sortable)
            <div class="flex flex-col gap-px opacity-40">
                <svg class="w-2.5 h-2.5 {{ $active && $direction === 'asc' ? 'opacity-100 text-violet-600' : '' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
                </svg>
                <svg class="w-2.5 h-2.5 {{ $active && $direction === 'desc' ? 'opacity-100 text-violet-600' : '' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        @endif
    </div>
</th>
