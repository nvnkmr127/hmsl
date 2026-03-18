@props([
    'label',
    'value',
    'badge'    => null,
    'badgeType'=> 'gray',   {{-- violet | success | warning | danger | gray --}}
    'icon'     => null,     {{-- SVG path string --}}
    'iconColor'=> '#7c3aed',
])

<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    <div class="flex items-start justify-between mb-4">
        @if($icon)
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(124,58,237,0.1)">
                <svg class="w-5 h-5" style="color:{{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/>
                </svg>
            </div>
        @else
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(124,58,237,0.1)">
                <svg class="w-5 h-5" style="color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        @endif

        @if($badge)
            <span class="badge badge-{{ $badgeType }}">{{ $badge }}</span>
        @endif
    </div>

    <p class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none mb-1"
       style="letter-spacing:-0.02em">{{ $value }}</p>
    <p class="text-sm font-600 text-gray-500 dark:text-gray-400">{{ $label }}</p>

    @if(isset($slot) && $slot->isNotEmpty())
        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">{{ $slot }}</div>
    @endif
</div>
