<div class="flex items-center justify-center min-h-[60vh]">
    <div class="max-w-md w-full bg-white dark:bg-gray-900 border border-gray-150 dark:border-gray-800 shadow-xl rounded-2xl p-8 space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Change Password</h2>
            <p class="text-xs text-red-500 mt-2 font-bold animate-pulse">Security Warning: You must change the default password to continue.</p>
        </div>

        @if (session()->has('message'))
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-bold">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="changePassword" class="space-y-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-2">New Password</label>
                <input type="password" wire:model="password" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-2">Confirm New Password</label>
                <input type="password" wire:model="password_confirmation" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-md active:scale-95">
                Update Password
            </button>
        </form>
    </div>
</div>
