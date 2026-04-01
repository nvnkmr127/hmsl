<div class="space-y-4">
    <x-form.input wire:model.live.debounce.300ms="search" placeholder="Search token, UHID, name, phone, or doctor..." />

    <div class="divide-y divide-gray-100 dark:divide-gray-800 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        @forelse($consultations as $c)
            <button type="button"
                    wire:click="selectConsultation({{ $c->id }})"
                    class="w-full text-left p-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors {{ $c->payment_status !== 'Paid' ? 'opacity-50' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">
                            {{ $c->consultation_date?->format('d M Y') }} · Token #{{ $c->token_number }}
                        </p>
                        <p class="text-sm font-black text-gray-900 dark:text-white truncate mt-1">{{ $c->patient->full_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $c->patient->uhid }} · {{ $c->doctor?->full_name ?? '—' }}</p>
                    </div>
                    <div class="text-right">
                        @if($c->payment_status === 'Paid')
                            <span class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Paid</span>
                        @else
                            <span class="text-xs font-black uppercase tracking-widest text-rose-600 dark:text-rose-400">Unpaid</span>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $c->status }}</p>
                    </div>
                </div>
            </button>
        @empty
            <div class="p-8 text-center text-sm font-semibold text-gray-500 dark:text-gray-400">
                No tokens found in the last 30 days.
            </div>
        @endforelse
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        Only paid tokens can generate a bill.
    </p>
</div>

