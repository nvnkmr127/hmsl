<div class="flex flex-wrap gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-4 mb-8">
    @php
        $navs = [
            ['route' => 'master.departments.index', 'label' => 'Depts'],
            ['route' => 'master.doctors.index', 'label' => 'Doctors'],
            ['route' => 'master.users.index', 'label' => 'Users'],
            ['route' => 'master.services.index', 'label' => 'Services'],
            ['route' => 'master.medicines.index', 'label' => 'Meds'],
            ['route' => 'master.labs.index', 'label' => 'Labs'],
            ['route' => 'master.wards.index', 'label' => 'Wards'],
            ['route' => 'master.beds.index', 'label' => 'Beds'],
            ['route' => 'master.inventory-categories.index', 'label' => 'Categories'],
            ['route' => 'inventory.suppliers', 'label' => 'Suppliers'],


        ];

    @endphp

    @foreach($navs as $nav)
        <a href="{{ route($nav['route']) }}" class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs($nav['route'] . '*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
            {{ $nav['label'] }}
        </a>
    @endforeach
</div>
