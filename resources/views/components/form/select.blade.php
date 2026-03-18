@props(['label' => null, 'name' => null, 'options' => [], 'error' => null])

<div class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest pl-1">{{ $label }}</label>
    @endif
    
    <div class="relative group">
        <select name="{{ $name }}" 
                id="{{ $name }}" 
                {{ $attributes->merge(['class' => 'block w-full rounded-2xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/20 focus:bg-white dark:focus:bg-gray-800 transition-all duration-300 px-4 py-3 sm:text-sm appearance-none']) }}>
            {{ $slot }}
        </select>
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 transition-colors group-focus-within:text-indigo-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </div>
    </div>

    @error($name)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-bold pl-1">{{ $message }}</p>
    @enderror
</div>
