<div>
    <x-card title="Lab Orders">
        <x-slot name="action">
            @unless($admission->status === 'Discharged')
                <button @click="$dispatch('open-lab-order', { admissionId: {{ $admission->id }} })" class="btn btn-primary text-xs">
                    Add
                </button>
            @endunless
        </x-slot>

        @if($this->labOrders->count() > 0)
            <div class="space-y-3">
                @foreach($this->labOrders as $order)
                    <div wire:key="ipd-lab-order-{{ $order->id }}" class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h5 class="font-bold text-gray-900 dark:text-white">{{ $order->labTest?->name }}</h5>
                                <p class="text-xs text-gray-500">Order #{{ $order->order_number }} · {{ $order->created_at->format('d M, h:i A') }}</p>
                                @if($order->doctor)
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-tight">Ordered by: {{ $order->doctor->user?->name }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full {{ 
                                    $order->status === 'Pending' ? 'bg-amber-100 text-amber-700' : 
                                    ($order->status === 'Completed' ? 'bg-indigo-100 text-indigo-700' : 
                                    'bg-emerald-100 text-emerald-700') 
                                }}">
                                    {{ $order->status }}
                                </span>
                                @if($order->status === 'Verified')
                                    <a href="#" class="text-[10px] font-bold text-indigo-600 hover:underline">View Report</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <p class="text-sm">No lab orders recorded yet.</p>
            </div>
        @endif
    </x-card>
</div>
