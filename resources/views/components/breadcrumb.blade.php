@props(['items' => []])

<nav class="flex items-center space-x-2 text-sm font-medium mb-6 animate-fade-in">
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
    </a>
    
    @foreach($items as $label => $link)
        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        @if($link)
            <a href="{{ $link }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $label }}</a>
        @else
            <span class="text-gray-700 dark:text-gray-200 font-bold uppercase tracking-wider text-[10px]">{{ $label }}</span>
        @endif
    @endforeach
</nav>
