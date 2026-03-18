@props(['name', 'title' => null, 'maxWidth' => '2xl'])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth] ?? 'sm:max-w-2xl';
@endphp

<div x-data="{ show: false }"
     x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
     x-on:close-modal.window="if ($event.detail.name === '{{ $name }}') show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show"
     class="fixed inset-0 z-[60] overflow-y-auto"
     style="display: none;"
     x-cloak>
    
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 transform transition-all" 
         @click="show = false">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md"></div>
    </div>

    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="px-4 py-8 mb-6 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700/50 transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto mt-10">
        
        <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-6 mb-6">
            @if($title)
                <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ $title }}</h3>
            @endif
            <button @click="show = false" class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" /></svg>
            </button>
        </div>

        <div class="px-6">
            {{ $slot }}
        </div>
    </div>
</div>
