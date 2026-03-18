@props([
    'title',
    'subtitle' => null,
    'back'     => null,   {{-- url for back link --}}
    'backLabel'=> 'Back',
])

{{-- Page Header — use at the top of every page --}}
<div class="mb-6">
    @if($back)
        <a href="{{ $back }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-900 dark:hover:text-white mb-4 transition-colors group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $backLabel }}
        </a>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
