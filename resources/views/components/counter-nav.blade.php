@props([
    'active' => null,
])

<div class="flex flex-wrap gap-3 border-b border-gray-100 dark:border-gray-700/50 pb-4 mb-8">
    @can('view patients')
    <a href="{{ route('counter.patients.index') }}"
       class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('counter.patients.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
        Patients
    </a>
    @endcan

    @can('view opd')
    <a href="{{ route('counter.opd.index') }}"
       class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('counter.opd.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
        Outpatient
    </a>
    @endcan

    @can('view ipd')
    <a href="{{ route('counter.ipd.index') }}"
       class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('counter.ipd.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
        Inpatient
    </a>
    @endcan

    @can('view billing')
    <a href="{{ route('billing.index') }}"
       class="px-6 py-3 rounded-2xl text-sm font-bold uppercase tracking-widest transition-all {{ request()->routeIs('billing.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
        Billing
    </a>
    @endcan
</div>

