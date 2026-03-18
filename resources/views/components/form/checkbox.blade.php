@props(['label' => null, 'name' => null, 'error' => null])

<div class="flex items-center space-x-3 group cursor-pointer">
    <div class="relative flex items-center">
        <input type="checkbox" 
               name="{{ $name }}" 
               id="{{ $name }}" 
               {{ $attributes->merge(['class' => 'peer w-5 h-5 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 focus:ring-4 focus:ring-indigo-500/10 focus:ring-offset-0 transition-all duration-300 appearance-none']) }}>
        
        <svg class="absolute w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100 left-1 transition-opacity duration-200" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
    </div>
    
    @if($label)
        <label for="{{ $name }}" class="text-sm font-bold text-gray-700 dark:text-gray-300 select-none group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors cursor-pointer">{{ $label }}</label>
    @endif

    @error($name)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-bold pl-1">{{ $message }}</p>
    @enderror
</div>
