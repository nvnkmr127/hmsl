<div>
    @if($admission->finalBill && $admission->finalBill->payments->count() > 0)
        <x-card title="Payment History" class="mt-4">
            <div class="space-y-3">
                @foreach($admission->finalBill->payments as $payment)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                ₹{{ number_format($payment->amount, 2) }}
                                @if($payment->type === 'refund')
                                    <span class="text-xs text-red-500 ml-1">(Refund)</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ ($payment->received_at ?? $payment->created_at)->format('d M, Y') }} &middot; {{ strtoupper($payment->method ?? 'Cash') }}
                            </p>
                        </div>
                        <a href="{{ route('counter.payments.print', $payment->id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-xs font-bold flex items-center gap-1 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print
                        </a>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
