@props(['label' => null, 'name' => null, 'placeholder' => '', 'rows' => 4, 'error' => null])

<div class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest pl-1">{{ $label }}</label>
    @endif
    
    <div class="relative group">
        <textarea name="{{ $name }}" 
                  id="{{ $name }}" 
                  rows="{{ $rows }}" 
                  placeholder="{{ $placeholder }}" 
                  {{ $attributes->merge(['class' => 'block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 focus:bg-white dark:focus:bg-gray-800 transition-all duration-300 px-4 py-3 sm:text-sm resize-none']) }}></textarea>
    </div>

    @error($name)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-bold pl-1">{{ $message }}</p>
    @enderror
</div>
