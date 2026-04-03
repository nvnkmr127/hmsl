@props(['title', 'icon' => 'home', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="mb-1">
    <button @click="open = !open" 
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200 group
                   {{ $active 
                       ? 'text-white bg-white/5' 
                       : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
        
        <div class="flex items-center gap-3">
            <x-icon :name="$icon" class="w-5 h-5 transition-transform group-hover:scale-110" />
            <span class="uppercase tracking-widest text-tiny">{{ $title }}</span>
        </div>

        <svg class="w-4 h-4 transition-transform duration-200" 
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="pl-11 mt-1 space-y-1">
        {{ $slot }}
    </div>
</div>
