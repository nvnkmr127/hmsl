<div class="mt-6">
    @if($conflicts->isNotEmpty())
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm rounded-2xl p-6">
            <div class="mb-4">
                <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Active Data Conflicts</h2>
                <p class="text-xs text-gray-400 mt-1">These records were updated on both the server and local client. Please select which version to preserve.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-850 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 uppercase text-[10px] tracking-wider font-bold">
                            <th class="pb-2 font-black">Table Name</th>
                            <th class="pb-2 font-black">Record UUID</th>
                            <th class="pb-2 font-black">Local Client Copy</th>
                            <th class="pb-2 font-black">Cloud Server Copy</th>
                            <th class="pb-2 font-black text-right">Resolve Option</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-850">
                        @foreach($conflicts as $conflict)
                            <tr class="align-top">
                                <td class="py-4 font-semibold text-gray-850 dark:text-gray-200">{{ $conflict->table_name }}</td>
                                <td class="py-4 text-xs text-gray-500 font-mono">{{ $conflict->record_uuid }}</td>
                                <td class="py-4">
                                    <pre class="text-[10px] bg-gray-50 dark:bg-gray-950 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800 overflow-auto max-w-[250px] max-h-[150px] font-mono text-gray-600 dark:text-gray-400">{{ json_encode($conflict->local_data, JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td class="py-4">
                                    <pre class="text-[10px] bg-gray-50 dark:bg-gray-950 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800 overflow-auto max-w-[250px] max-h-[150px] font-mono text-gray-600 dark:text-gray-400">{{ json_encode($conflict->server_data, JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td class="py-4 text-right space-y-2">
                                    <div class="flex flex-col space-y-2 justify-end items-end">
                                        <button wire:click="resolve({{ $conflict->id }}, 'local')" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md active:scale-95">Keep Local</button>
                                        <button wire:click="resolve({{ $conflict->id }}, 'server')" class="px-3.5 py-2 rounded-xl bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold transition-all shadow-md active:scale-95">Keep Server</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
