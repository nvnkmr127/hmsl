@props(['label' => null, 'name' => null, 'placeholder' => '', 'type' => 'text', 'error' => null, 'icon' => null, 'helpText' => null])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">{{ $label }}</label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
        @endif
        <input type="{{ $type }}"
               name="{{ $name }}"
               id="{{ $name }}"
               placeholder="{{ $placeholder }}"
               {{ $attributes->merge(['class' => 'input-field ' . ($icon ? 'pl-11' : '')]) }}>
    </div>

    @if($helpText)
        <p class="text-xs text-slate-400">{{ $helpText }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-500 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
