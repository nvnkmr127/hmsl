@props(['href', 'active' => false, 'icon' => 'home'])

<a href="{{ $href }}" wire:navigate
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-200 mb-1 group
          {{ $active
              ? 'bg-violet-600 text-white shadow-lg shadow-violet-900/40 translate-x-1'
              : 'text-gray-400 hover:text-white hover:bg-white/5 active:scale-95' }}">

    <x-icon :name="$icon" class="w-5 h-5 transition-transform group-hover:scale-110" />

    <span class="uppercase tracking-widest text-tiny">{{ $slot }}</span>
</a>
