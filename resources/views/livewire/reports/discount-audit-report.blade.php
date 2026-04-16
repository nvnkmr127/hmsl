<div>
    <x-breadcrumb :items="['Reports' => route('reports.index'), 'Discount Audit' => null]" />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Discount Audit Trail</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Verify transparency and financial integrity of all applied bill discounts.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[300px]">
                <x-form.input
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search by bill number or patient name..."
                    id="discount-search"
                />
            </div>
            <select wire:model.live="statusFilter"
                    class="px-4 py-2.5 rounded-xl border-transparent bg-gray-100/50 dark:bg-gray-700/50 text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending Approval</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="glass-card overflow-hidden">
        <x-table.wrapper>
            <thead>
                <tr>
                    <x-table.th>Date</x-table.th>
                    <x-table.th>Bill Details</x-table.th>
                    <x-table.th>Patient</x-table.th>
                    <x-table.th class="text-right">Discount</x-table.th>
                    <x-table.th>Applied By</x-table.th>
                    <x-table.th>Authorizer / Approver</x-table.th>
                    <x-table.th>Reason</x-table.th>
                    <x-table.th>Status</x-table.th>
                </tr>
            </thead>
            <tbody>
                @forelse($discounts as $discount)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $discount->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400 font-mono">{{ $discount->created_at->format('h:i A') }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-mono text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 rounded-lg">
                                {{ $discount->bill->bill_number }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $discount->bill->patient->full_name }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $discount->bill->patient->uhid }}</p>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <p class="text-sm font-black text-gray-900 dark:text-white">₹{{ number_format($discount->applied_amount, 2) }}</p>
                            <p class="text-[10px] text-gray-400 font-bold">
                                {{ $discount->discount_type === 'percentage' ? $discount->discount_value . '%' : 'Flat' }}
                            </p>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-[10px] font-black text-gray-500">
                                    {{ strtoupper(substr($discount->appliedBy->name ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $discount->appliedBy->name ?? 'Unknown' }}</p>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ $discount->appliedBy->roles->first()->name ?? 'Staff' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($discount->approver)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-[10px] font-black text-emerald-600">
                                        {{ strtoupper(substr($discount->approver->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $discount->approver->name }}</p>
                                        <p class="text-[10px] text-emerald-500/80 font-black uppercase tracking-widest">Approved By</p>
                                    </div>
                                </div>
                            @elseif($discount->doctor)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-[10px] font-black text-violet-600">
                                        DR
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200">Dr. {{ $discount->doctor->full_name }}</p>
                                        <p class="text-[10px] text-violet-500/80 font-black uppercase tracking-widest">Clinically Authorized</p>
                                    </div>
                                </div>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-300 italic">Self-Applied / Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-1 italic" title="{{ $discount->reason }}">
                                "{{ $discount->reason }}"
                            </p>
                        </td>
                        <td class="px-4 py-4">
                            @if($discount->status === 'approved')
                                <x-badge color="success">Verified</x-badge>
                            @elseif($discount->status === 'pending')
                                <div class="flex items-center gap-2">
                                    <x-badge color="warning">Pending Approval</x-badge>
                                    @if(Auth::user()->hasAnyRole(['admin', 'super_admin']) || \App\Models\Doctor::where('user_id', Auth::id())->exists())
                                        <button wire:click="approve({{ $discount->id }})" class="p-1 hover:bg-emerald-50 text-emerald-600 rounded-lg transition-colors" title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button wire:click="reject({{ $discount->id }})" class="p-1 hover:bg-rose-50 text-rose-600 rounded-lg transition-colors" title="Reject">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <x-badge color="danger">Rejected</x-badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-table.empty colspan="8" message="No discounts found for the selected criteria." />
                @endforelse
            </tbody>
        </x-table.wrapper>

        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700/50">
            {{ $discounts->links() }}
        </div>
    </div>
</div>
