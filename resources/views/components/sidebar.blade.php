<div class="flex items-center gap-3 px-6 h-16 border-b border-white/5 flex-shrink-0">
    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/20" 
         style="background:#7c3aed">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-white font-black text-xs leading-none uppercase tracking-[0.2em]">{{ config('app.name','HMS') }}</p>
        <p class="text-[9px] font-black mt-1 uppercase tracking-widest" style="color:#a78bfa">Medical Suite</p>
    </div>
    <button @click="sidebarOpen = false"
            class="lg:hidden w-8 h-8 rounded-xl flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/5 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<nav class="flex-1 overflow-y-auto py-6 px-4 space-y-8 custom-scrollbar">
    <!-- Executive -->
    <div>
        <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] px-3 mb-4">Operations</p>
        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">Console</x-nav-link>
    </div>

    <!-- Reception -->
    <div>
        <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] px-3 mb-4">Front Desk</p>
        <x-nav-link href="{{ route('counter.patients.index') }}" :active="request()->routeIs('counter.patients.*')" icon="users">Registry</x-nav-link>
        <x-nav-link href="{{ route('counter.opd.index') }}" :active="request()->routeIs('counter.opd.*')" icon="calendar">OPD Desk</x-nav-link>
        <x-nav-link href="{{ route('counter.ipd.index') }}" :active="request()->routeIs('counter.ipd.*')" icon="bed">IPD Admission</x-nav-link>
        <x-nav-link href="{{ route('billing.index') }}" :active="request()->routeIs('billing.*')" icon="credit-card">Payments</x-nav-link>
    </div>

    <!-- Clinical -->
    <div>
        <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] px-3 mb-4">Medical</p>
        <x-nav-link href="{{ route('doctor.dashboard') }}" :active="request()->routeIs('doctor.dashboard')" icon="stethoscope">Doctor Desk</x-nav-link>
        <x-nav-link href="{{ route('doctor.appointments.index') }}" :active="request()->routeIs('doctor.appointments.*')" icon="calendar">Appointments</x-nav-link>
        <x-nav-link href="{{ route('doctor.patients.index') }}" :active="request()->routeIs('doctor.patients.*')" icon="users">Patient Records</x-nav-link>
        <x-nav-link href="{{ route('laboratory.index') }}" :active="request()->routeIs('laboratory.*')" icon="flask">Laboratory</x-nav-link>
        <x-nav-link href="{{ route('pharmacy.index') }}" :active="request()->routeIs('pharmacy.*')" icon="pill">Pharmacy</x-nav-link>
    </div>

    <!-- Master Data -->
    <div>
        <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] px-3 mb-4">Governance</p>
        <x-nav-link href="{{ route('master.doctors.index') }}" :active="request()->routeIs('master.doctors.*')" icon="users">Manage Staff</x-nav-link>
        <x-nav-link href="{{ route('master.users.index') }}" :active="request()->routeIs('master.users.*')" icon="users">User Accounts</x-nav-link>
        <x-nav-link href="{{ route('inventory.index') }}" :active="request()->routeIs('inventory.*')" icon="box">Inventory</x-nav-link>
        <x-nav-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')" icon="chart">Analytics</x-nav-link>
        <x-nav-link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')" icon="settings">System Control</x-nav-link>
    </div>
</nav>

<div class="flex-shrink-0 p-4 border-t border-white/5">
    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/5">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black text-white shadow-inner"
             style="background: linear-gradient(135deg, #7c3aed, #4c1d95)">
            {{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-white truncate uppercase tracking-tight">{{ Auth::user()?->name ?? 'Administrator' }}</p>
            <p class="text-[9px] font-black text-violet-400 uppercase tracking-widest">Sys Admin</p>
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
