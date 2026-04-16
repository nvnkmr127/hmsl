<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">From Date</label>
                <input wire:model.live="dateFrom" type="date" class="block w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary-500 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">To Date</label>
                <input wire:model.live="dateTo" type="date" class="block w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary-500 transition-all outline-none">
            </div>
        </div>
    </div>

    <div class="overflow-hidden border border-slate-200 dark:border-slate-800 rounded-3xl bg-white dark:bg-slate-900 shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Doctor</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Department</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Consults</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Earnings</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($doctors as $doctor)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Dr. {{ $doctor->full_name }}</div>
                        <div class="text-[10px] text-slate-500 font-medium uppercase tracking-tighter">{{ $doctor->specialization }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                            {{ $doctor->department->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-black text-slate-900 dark:text-white">
                        {{ number_format($doctor->consultations_count) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-emerald-600 dark:text-emerald-400">
                        ₹{{ number_format($doctor->total_earnings, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
