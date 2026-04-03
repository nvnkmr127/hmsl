<div class="flex items-center gap-3 px-6 h-20 border-b border-white/5 flex-shrink-0 bg-gray-900/50 backdrop-blur-xl">
    <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-2xl shadow-violet-500/40 bg-gradient-to-br from-violet to-violet-dk border border-white/10">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-white font-black text-[10px] leading-none uppercase tracking-[0.3em] mb-1.5">{{ config('app.name','Children Clinic') }}</p>
        <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-violet-lt/80">Specialized Pediatric Care</p>
        </div>
    </div>
    <button @click="sidebarOpen = false"
            class="lg:hidden w-8 h-8 rounded-xl flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/5 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<nav class="flex-1 overflow-y-auto py-8 px-4 space-y-9 custom-scrollbar scroll-smooth">
    <!-- Main Menu -->
    <div>
        <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] px-3 mb-5">Command Center</p>
        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">Overview</x-nav-link>
    </div>

    <!-- Front Desk -->
    @canany(['view patients', 'view opd', 'view ipd', 'view billing'])
    <div>
        <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] px-3 mb-5">Registration & Billing</p>
        @can('view patients')
        <x-nav-link href="{{ route('counter.patients.index') }}" :active="request()->routeIs('counter.patients.*')" icon="users">Patients</x-nav-link>
        @endcan
        @can('view opd')
        <x-nav-link href="{{ route('counter.opd.index') }}" :active="request()->routeIs('counter.opd.*')" icon="calendar">Outpatient</x-nav-link>
        @endcan
        @can('view ipd')
        <x-nav-link href="{{ route('counter.ipd.index') }}" :active="request()->routeIs('counter.ipd.*')" icon="bed">Inpatient</x-nav-link>
        @endcan
        @can('view billing')
        <x-nav-link href="{{ route('billing.index') }}" :active="request()->routeIs('billing.*')" icon="credit-card">Billing</x-nav-link>
        @endcan
    </div>
    @endcanany

    <!-- Clinical -->
    @canany(['edit case sheets', 'view patients', 'view lab', 'view pharmacy', 'admit patients'])
    <div>
        <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] px-3 mb-5">Clinical Services</p>
        @can('edit case sheets')
        <x-nav-link href="{{ route('doctor.dashboard') }}" :active="request()->routeIs('doctor.dashboard')" icon="stethoscope">Doctor</x-nav-link>
        <x-nav-link href="{{ route('doctor.appointments.index') }}" :active="request()->routeIs('doctor.appointments.*')" icon="calendar">Appointments</x-nav-link>
        @endcan

        @can('view patients')
        <x-nav-link href="{{ route('doctor.patients.index') }}" :active="request()->routeIs('doctor.patients.*')" icon="users">Patient Files</x-nav-link>
        @endcan
        
        @can('view lab')
        <x-nav-link href="{{ route('laboratory.index') }}" :active="request()->routeIs('laboratory.*')" icon="flask">Lab</x-nav-link>
        @endcan
        @can('view pharmacy')
        <x-nav-link href="{{ route('pharmacy.index') }}" :active="request()->routeIs('pharmacy.*')" icon="pill">Pharmacy</x-nav-link>
        @endcan
        
        @can('admit patients')
        <x-nav-link href="{{ route('discharge.index') }}" :active="request()->routeIs('discharge.*')" icon="logout">Discharges</x-nav-link>
        @endcan
    </div>
    @endcanany

    <!-- Reports -->
    @can('view reports')
    <div>
        <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] px-3 mb-5">Data Intelligence</p>
        <x-nav-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')" icon="chart">Reports</x-nav-link>
    </div>
    @endcan

    <!-- Management -->
    @canany(['manage master data', 'manage users', 'manage inventory', 'manage settings'])
    <div>
        <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] px-3 mb-5">System Control</p>
        
        @can('manage master data')
        <x-nav-group title="Master Data" icon="box" :active="request()->routeIs('master.*')">
            <x-nav-link href="{{ route('master.doctors.index') }}" :active="request()->routeIs('master.doctors.*')" icon="users">Doctors</x-nav-link>
            <x-nav-link href="{{ route('master.departments.index') }}" :active="request()->routeIs('master.departments.*')" icon="home">Departments</x-nav-link>
            <x-nav-link href="{{ route('master.wards.index') }}" :active="request()->routeIs('master.wards.*')" icon="bed">Wards & Beds</x-nav-link>
            <x-nav-link href="{{ route('master.services.index') }}" :active="request()->routeIs('master.services.*')" icon="credit-card">Services</x-nav-link>
            <x-nav-link href="{{ route('master.medicines.index') }}" :active="request()->routeIs('master.medicines.*')" icon="pill">Medicines</x-nav-link>
            <x-nav-link href="{{ route('master.labs.index') }}" :active="request()->routeIs('master.labs.*')" icon="beaker">Lab Tests</x-nav-link>
            <x-nav-link href="{{ route('master.clinical-templates.index') }}" :active="request()->routeIs('master.clinical-templates.*')" icon="clipboard">Admission Layouts</x-nav-link>
        </x-nav-group>
        @endcan

        @can('manage users')
        <x-nav-link href="{{ route('master.users.index') }}" :active="request()->routeIs('master.users.*')" icon="users">Users</x-nav-link>
        @endcan
        
        @can('manage inventory')
        <x-nav-link href="{{ route('inventory.index') }}" :active="request()->routeIs('inventory.*')" icon="box">Stock</x-nav-link>
        @endcan
        
        @can('manage settings')
        <x-nav-group title="Settings" icon="settings" :active="request()->routeIs('settings.*')">
            <x-nav-link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.index')" icon="home">Hospital Details</x-nav-link>
            <x-nav-link href="{{ route('settings.preferences') }}" :active="request()->routeIs('settings.preferences')" icon="monitor">Preferences</x-nav-link>
            <x-nav-link href="{{ route('settings.webhooks.index') }}" :active="request()->routeIs('settings.webhooks.*')" icon="link">Webhooks</x-nav-link>
            <x-nav-link href="{{ route('settings.api-tokens.index') }}" :active="request()->routeIs('settings.api-tokens.*')" icon="credit-card">API Tokens</x-nav-link>
        </x-nav-group>
        @endcan
    </div>
    @endcanany
</nav>

<div class="flex-shrink-0 p-4 border-t border-white/5">
    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/5">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black text-white shadow-inner bg-gradient-to-br from-violet to-violet-dk">
            {{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-white truncate uppercase tracking-tight">{{ Auth::user()?->name ?? 'Administrator' }}</p>
            <p class="text-dense font-black text-violet-lt/60 uppercase tracking-widest">{{ Auth::user()?->role_name }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</div>
