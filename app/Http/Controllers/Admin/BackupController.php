<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BackupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $localBackups = $this->backupService->getLocalBackups();
        
        $settings = DB::table('settings')
            ->whereIn('key', [
                'gdrive_enabled', 'gdrive_client_id', 'gdrive_client_secret', 'gdrive_folder_name', 'backup_retention_days',
                'auto_backup', 'backup_frequency', 'backup_notifications', 'notification_email'
            ])
            ->pluck('value', 'key')
            ->toArray();

        $gdriveEnabled = ($settings['gdrive_enabled'] ?? '0') === '1';
        $gdriveBackups = [];
        $gdriveConnected = false;

        if ($gdriveEnabled) {
            $gdriveConnected = $this->backupService->testGoogleDriveConnection();
            if ($gdriveConnected) {
                $gdriveBackups = $this->backupService->getGoogleDriveBackups();
            }
        }

        return view('admin.backups.index', compact('localBackups', 'gdriveBackups', 'settings', 'gdriveEnabled', 'gdriveConnected'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:database,code,settings,all'
        ]);

        try {
            $files = [];
            $type = $request->input('type');

            if ($type === 'database' || $type === 'all') {
                $files[] = $this->backupService->createDatabaseBackup('manual');
            }
            if ($type === 'code' || $type === 'all') {
                $files[] = $this->backupService->createCodeBackup();
            }
            if ($type === 'settings' || $type === 'all') {
                $files[] = $this->backupService->createSettingsBackup();
            }

            Log::info("Backup created manually", ['type' => $type, 'files' => $files, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully.',
                'files' => $files
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createManualBackup(Request $request)
    {
        return $this->store($request);
    }

    public function download($fileName)
    {
        $filePath = storage_path('app/backups/' . $fileName);
        
        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($filePath);
    }

    public function destroy(Request $request, $id)
    {
        $source = $request->input('source', 'local');

        try {
            if ($source === 'local') {
                $filePath = storage_path('app/backups/' . $id);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            } elseif ($source === 'gdrive') {
                $this->backupService->deleteFromGoogleDrive($id);
            }

            Log::info("Backup deleted", ['filename' => $id, 'source' => $source, 'user_id' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Backup deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'gdrive_enabled' => 'nullable|boolean',
            'gdrive_client_id' => 'nullable|string',
            'gdrive_client_secret' => 'nullable|string',
            'gdrive_folder_name' => 'nullable|string',
            'backup_retention_days' => 'nullable|integer|min:1',
            'auto_backup' => 'nullable|boolean',
            'backup_frequency' => 'nullable|in:daily,weekly,monthly',
            'backup_notifications' => 'nullable|boolean',
            'notification_email' => 'nullable|email'
        ]);

        $settings = $request->only([
            'gdrive_enabled', 'gdrive_client_id', 'gdrive_client_secret', 'gdrive_folder_name', 
            'backup_retention_days', 'auto_backup', 'backup_frequency', 'backup_notifications', 'notification_email'
        ]);
        
        $settings['gdrive_enabled'] = $request->has('gdrive_enabled') ? '1' : '0';
        $settings['auto_backup'] = $request->has('auto_backup') ? '1' : '0';
        $settings['backup_notifications'] = $request->has('backup_notifications') ? '1' : '0';

        if (!empty($settings['gdrive_client_secret'])) {
            $settings['gdrive_client_secret'] = encrypt($settings['gdrive_client_secret']);
        } else {
            unset($settings['gdrive_client_secret']);
        }

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => (string) $value]
                );
            }
        }

        // For AJAX requests we return JSON
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Backup settings updated.']);
        }
        
        return back()->with('success', 'Backup settings updated.');
    }

    public function cleanupBackups(Request $request)
    {
        try {
            $days = (int) DB::table('settings')->where('key', 'backup_retention_days')->value('value') ?: 30;
            $deleted = $this->backupService->cleanupOldBackups($days);
            
            return response()->json([
                'success' => true, 
                'message' => "Cleaned up {$deleted} old backup files."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restoreDatabase(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        try {
            $this->backupService->restoreDatabaseBackup($request->filename);
            Log::info("Database restored", ['filename' => $request->filename, 'user_id' => auth()->id()]);
            return response()->json(['success' => true, 'message' => 'Database restored successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restoreSettings(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        try {
            $this->backupService->restoreSettingsBackup($request->filename);
            Log::info("Settings restored", ['filename' => $request->filename, 'user_id' => auth()->id()]);
            return response()->json(['success' => true, 'message' => 'Settings restored successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function authorizeGoogleDrive()
    {
        $client = $this->backupService->getGoogleDriveClient();
        $authUrl = $client->createAuthUrl();
        
        return response()->json(['auth_url' => $authUrl]);
    }

    public function handleGoogleDriveCallback(Request $request)
    {
        $code = $request->input('code');
        
        if ($code) {
            $client = $this->backupService->getGoogleDriveClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);
            
            if (!isset($token['error'])) {
                if (isset($token['access_token'])) {
                    DB::table('settings')->updateOrInsert(
                        ['key' => 'gdrive_access_token'],
                        ['value' => encrypt($token['access_token'])]
                    );
                }
                if (isset($token['refresh_token'])) {
                    DB::table('settings')->updateOrInsert(
                        ['key' => 'gdrive_refresh_token'],
                        ['value' => encrypt($token['refresh_token'])]
                    );
                }
                
                return redirect()->route('admin.backups.index')->with('success', 'Google Drive connected successfully.');
            }
        }
        
        return redirect()->route('admin.backups.index')->with('error', 'Failed to connect Google Drive.');
    }

    public function testGoogleDriveConnection()
    {
        $success = $this->backupService->testGoogleDriveConnection();
        return response()->json(['success' => $success]);
    }

    public function listGoogleDriveBackups()
    {
        $backups = $this->backupService->getGoogleDriveBackups();
        return response()->json($backups);
    }

    public function uploadToGoogleDrive(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        try {
            $filePath = storage_path('app/backups/' . $request->filename);
            if (!File::exists($filePath)) {
                throw new \Exception("Local backup file not found.");
            }

            $fileId = $this->backupService->uploadToGoogleDrive($filePath, $request->filename);
            
            return response()->json([
                'success' => true, 
                'message' => 'Uploaded to Google Drive successfully.',
                'file_id' => $fileId
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
