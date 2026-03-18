@extends('layouts.auth')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <!-- Email -->
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wide">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               placeholder="you@hospital.com"
               class="input-field @error('email') !border-red-400 @enderror">
        @error('email')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password -->
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wide">Password</label>
        <input type="password" name="password" required autocomplete="current-password"
               placeholder="••••••••"
               class="input-field @error('password') !border-red-400 @enderror">
        @error('password')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Remember + Forgot -->
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="remember"
                   class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500/20">
            <span class="text-sm text-slate-500">Remember me</span>
        </label>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}"
               class="text-sm font-semibold hover:underline" style="color:#7c3aed">
                Forgot password?
            </a>
        @endif
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-full justify-center text-sm py-2.5 mt-2">
        Sign In
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </button>
</form>

<!-- Quick access -->
<div class="mt-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 h-px bg-slate-200 dark:bg-white/10"></div>
        <span class="text-xs text-slate-400 font-medium">Quick Access</span>
        <div class="flex-1 h-px bg-slate-200 dark:bg-white/10"></div>
    </div>
    <div class="grid grid-cols-2 gap-2">
        @foreach([['admin','Owner','Admin'],['doctor','Doctor','Clinical'],['counter','Counter','Reception'],['nurse','Nurse','Staff']] as [$role,$label,$dept])
        <a href="{{ route('autologin', ['role' => $role]) }}"
           class="p-3 rounded-xl border border-slate-200 dark:border-white/10 hover:border-violet-300 hover:bg-violet-50 dark:hover:bg-violet-900/10 transition-all group">
            <p class="text-[10px] font-bold text-slate-400 group-hover:text-violet-500 uppercase tracking-wider mb-0.5">{{ $dept }}</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</p>
        </a>
        @endforeach
    </div>
</div>
@endsection
