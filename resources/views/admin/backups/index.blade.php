@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
@php
    $totalLocalBackups = count($localBackups);
    $totalLocalSize = array_sum(array_column($localBackups, 'size'));
    $lastBackupTime = empty($localBackups) ? 'N/A' : date('Y-m-d H:i:s', $localBackups[0]['date']);
@endphp
<div class="space-y-6" x-data="backupManager()">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Backup & Restore</h1>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Local Backups</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalLocalBackups }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Size</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalLocalSize / 1048576, 2) }} MB</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Backup</h3>
            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ $lastBackupTime }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">GDrive Status</h3>
            <p class="mt-2 text-xl font-semibold {{ $gdriveConnected ? 'text-green-600' : 'text-yellow-600' }}">
                {{ $gdriveEnabled ? ($gdriveConnected ? 'Connected' : 'Disconnected') : 'Disabled' }}
            </p>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-4 right-4 z-50 p-4 rounded-md shadow-lg"
         :class="toast.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
         style="display: none;">
        <p x-text="toast.message" class="font-medium"></p>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sidebar Column (Settings & Create Backup) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Create Backup Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 relative">
                <!-- Overlay Spinner -->
                <div x-show="isProcessing" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center rounded-lg z-10" style="display: none;">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create Backup</h3>
                <div class="grid grid-cols-1 gap-3">
                    <button @click="createBackup('database')" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Backup Database
                    </button>
                    <button @click="createBackup('code')" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                        Backup Code
                    </button>
                    <button @click="createBackup('settings')" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        Backup Settings
                    </button>
                    <button @click="createBackup('all')" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-800 hover:bg-gray-900 dark:bg-gray-600 dark:hover:bg-gray-700">
                        Backup All
                    </button>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 relative">
                <div x-show="isSavingSettings" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center rounded-lg z-10" style="display: none;">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Settings</h3>
                
                <form @submit.prevent="saveSettings" id="settingsForm" class="space-y-4">
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="auto_backup" id="auto_backup" value="1" {{ ($settings['auto_backup'] ?? '0') === '1' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="auto_backup" class="ml-2 block text-sm font-medium text-gray-900 dark:text-gray-300">Enable Auto Backup</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Backup Frequency</label>
                        <select name="backup_frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="daily" {{ ($settings['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ ($settings['backup_frequency'] ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ ($settings['backup_frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Retention Days</label>
                        <input type="number" name="backup_retention_days" value="{{ $settings['backup_retention_days'] ?? 30 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    </div>

                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="backup_notifications" id="backup_notifications" value="1" {{ ($settings['backup_notifications'] ?? '0') === '1' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="backup_notifications" class="ml-2 block text-sm font-medium text-gray-900 dark:text-gray-300">Email Notifications</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notification Email</label>
                        <input type="email" name="notification_email" value="{{ $settings['notification_email'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" name="gdrive_enabled" id="gdrive_enabled" value="1" {{ $gdriveEnabled ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="gdrive_enabled" class="ml-2 block text-sm font-medium text-gray-900 dark:text-gray-300">Enable Google Drive</label>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">Client ID</label>
                                <input type="text" name="gdrive_client_id" value="{{ $settings['gdrive_client_id'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">Client Secret</label>
                                <input type="password" name="gdrive_client_secret" value="{{ $settings['gdrive_client_secret'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">Folder Name</label>
                                <input type="text" name="gdrive_folder_name" value="{{ $settings['gdrive_folder_name'] ?? 'App-Backups' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Save Settings
                        </button>
                        
                        <button type="button" @click="authorizeGoogleDrive" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                            Authorize Google Drive
                        </button>
                        
                        <button type="button" @click="testGoogleDrive" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            Test GDrive Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tables Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Local Backups -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden relative">
                <div x-show="isTableProcessing" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center rounded-lg z-10" style="display: none;">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Local Backups</h3>
                    <div class="flex items-center space-x-2">
                        <input type="file" x-ref="backupFile" @change="uploadBackup" class="hidden" accept=".sql,.zip,.json">
                        <button @click="$refs.backupFile.click()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Upload Backup
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Filename</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($localBackups as $backup)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $backup['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize">
                                    {{ $backup['type'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($backup['size'] / 1048576, 2) }} MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ date('Y-m-d H:i:s', $backup['date']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    
                                    @if($gdriveConnected)
                                        <button @click="uploadToGoogleDrive('{{ $backup['name'] }}')" class="text-purple-600 hover:text-purple-900 dark:hover:text-purple-400" title="Upload to Google Drive">
                                            Upload
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.backups.download', $backup['name']) }}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400" title="Download">
                                        Download
                                    </a>

                                    @if($backup['type'] === 'database' || $backup['type'] === 'settings')
                                    <button @click="confirmRestore('{{ $backup['name'] }}', '{{ $backup['type'] }}')" class="text-green-600 hover:text-green-900 dark:hover:text-green-400" title="Restore">
                                        Restore
                                    </button>
                                    @endif

                                    <button @click="deleteBackup('{{ $backup['name'] }}', 'local')" class="text-red-600 hover:text-red-900 dark:hover:text-red-400" title="Delete">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No local backups found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- GDrive Backups -->
            @if($gdriveEnabled)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden relative">
                <div x-show="isTableProcessing" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center rounded-lg z-10" style="display: none;">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Google Drive Backups</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Filename</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($gdriveBackups as $file)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $file->getName() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($file->getSize() / 1048576, 2) }} MB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ date('Y-m-d H:i:s', strtotime($file->getCreatedTime())) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button @click="deleteBackup('{{ $file->getId() }}', 'gdrive')" class="text-red-600 hover:text-red-900 dark:hover:text-red-400" title="Delete">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No Google Drive backups found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div x-show="restoreModal.show" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="restoreModal.show = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-90"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Restore Backup</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    This will overwrite your current <span x-text="restoreModal.type" class="font-bold"></span>. All existing data will be lost. Continue?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="executeRestore" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Restore
                    </button>
                    <button type="button" @click="restoreModal.show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function backupManager() {
    return {
        toast: { show: false, message: '', type: '' },
        isProcessing: false,
        isTableProcessing: false,
        isSavingSettings: false,
        restoreModal: { show: false, filename: '', type: '' },
        
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 5000);
        },

        async ajaxRequest(url, method = 'POST', data = null, stateProp = 'isProcessing') {
            this[stateProp] = true;
            try {
                const options = {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                };
                if (data) options.body = JSON.stringify(data);

                const response = await fetch(url, options);
                const result = await response.json();
                
                this[stateProp] = false;
                
                if (result.success) {
                    this.showToast(result.message, 'success');
                    if (stateProp !== 'isSavingSettings' && method !== 'GET') {
                        // Reload data to reflect changes
                        setTimeout(() => window.location.reload(), 1000);
                    }
                    return result;
                } else {
                    this.showToast(result.message || 'An error occurred', 'error');
                    return null;
                }
            } catch (error) {
                this[stateProp] = false;
                this.showToast('Network error occurred.', 'error');
                return null;
            }
        },

        async createBackup(type) {
            await this.ajaxRequest('{{ route("admin.backups.manual") }}', 'POST', { type }, 'isProcessing');
        },

        async saveSettings() {
            const formData = new FormData(document.getElementById('settingsForm'));
            const data = Object.fromEntries(formData.entries());
            data._method = 'PUT';
            
            // Checkboxes aren't included if unchecked, so we handle them server-side via checking presence.
            await this.ajaxRequest('{{ route("admin.backups.settings") }}', 'POST', data, 'isSavingSettings');
        },

        confirmRestore(filename, type) {
            this.restoreModal = { show: true, filename, type };
        },

        async executeRestore() {
            this.restoreModal.show = false;
            const route = this.restoreModal.type === 'database' 
                ? '{{ route("admin.backups.restore.database") }}'
                : '{{ route("admin.backups.restore.settings") }}';
            
            await this.ajaxRequest(route, 'POST', { filename: this.restoreModal.filename }, 'isTableProcessing');
        },

        async deleteBackup(id, source) {
            if(confirm('Are you sure you want to delete this backup?')) {
                await this.ajaxRequest(`{{ url('admin/backups') }}/${id}`, 'POST', { source, _method: 'DELETE' }, 'isTableProcessing');
            }
        },

        async uploadBackup(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('file', file);
            
            this.isTableProcessing = true;
            try {
                const response = await fetch('{{ route("admin.backups.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                this.isTableProcessing = false;
                
                if (result.success) {
                    this.showToast(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast(result.message || 'Upload failed', 'error');
                }
            } catch (e) {
                this.isTableProcessing = false;
                this.showToast('Upload failed', 'error');
            }
        },

        async uploadToGoogleDrive(filename) {
            await this.ajaxRequest('{{ route("admin.backups.gdrive.upload") }}', 'POST', { filename }, 'isTableProcessing');
        },

        async testGoogleDrive() {
            this.isSavingSettings = true;
            try {
                const response = await fetch('{{ route("admin.backups.gdrive.test") }}');
                const result = await response.json();
                this.isSavingSettings = false;
                if(result.success) {
                    this.showToast('Google Drive connection successful!', 'success');
                } else {
                    this.showToast('Google Drive connection failed or token expired.', 'error');
                }
            } catch (e) {
                this.isSavingSettings = false;
                this.showToast('Connection test failed.', 'error');
            }
        },

        async authorizeGoogleDrive() {
            this.isSavingSettings = true;
            try {
                const response = await fetch('{{ route("admin.backups.gdrive.authorize") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const result = await response.json();
                if(result.auth_url) {
                    window.location.href = result.auth_url;
                } else {
                    this.isSavingSettings = false;
                    this.showToast('Failed to generate auth URL.', 'error');
                }
            } catch (error) {
                this.isSavingSettings = false;
                this.showToast('Failed to generate auth URL.', 'error');
            }
        }
    }
}
</script>
@endsection
