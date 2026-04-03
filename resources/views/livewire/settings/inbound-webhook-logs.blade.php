<div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Inbound Webhook History</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Time</th>
                    <th class="px-6 py-4 font-semibold">Source</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $log->created_at->format('M d, H:i:s') }}
                            </span>
                            <div class="text-xs text-gray-400 font-mono">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase tracking-tight">
                                {{ $log->source }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg 
                                {{ $log->status === 'completed' ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 
                                   ($log->status === 'failed' ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400') }} uppercase tracking-tight">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-sm font-bold">Details</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-6 border-t border-gray-100 dark:border-gray-700">
        {{ $logs->links() }}
    </div>
</div>
