<div class="flex flex-wrap gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-4 mb-8">
    @php
        $settingsNav = [
            ['route' => 'settings.index', 'label' => 'Hospital Details'],
            ['route' => 'settings.preferences', 'label' => 'System Preferences'],
            ['route' => 'settings.invoice', 'label' => 'Invoice & Print'],
            ['route' => 'settings.webhooks.index', 'label' => 'Webhooks'],
            ['route' => 'settings.api-tokens.index', 'label' => 'API Tokens'],
            ['route' => 'settings.sync.index', 'label' => 'Offline Sync'],
        ];
    @endphp

    @foreach($settingsNav as $nav)
        <a href="{{ route($nav['route']) }}" 
            class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-[0.2em] transition-all duration-300 {{ request()->routeIs($nav['route'] . '*') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/40 translate-y-[-2px]' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-indigo-600' }}">
            {{ $nav['label'] }}
        </a>
    @endforeach
</div>
