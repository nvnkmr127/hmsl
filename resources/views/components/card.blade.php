@props([
    'title'    => null,
    'subtitle' => null,
    'action'   => null,
    'noPad'    => false,
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>

    @if($title || isset($action))
        <div class="panel-hd">
            <div>
                @if($title)
                    <p class="panel-title">{{ $title }}</p>
                @endif
                @if($subtitle)
                    <p class="panel-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($action))
                <div class="flex items-center gap-2 flex-shrink-0">{{ $action }}</div>
            @endif
        </div>
    @endif

    <div class="{{ $noPad ? '' : 'p-5 sm:p-6' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
            {{ $footer }}
        </div>
    @endif
</div>
