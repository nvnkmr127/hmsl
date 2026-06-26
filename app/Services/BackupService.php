<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class BackupService
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function createDatabaseBackup(string $type = 'manual')
    {
        $freeSpace = disk_free_space($this->backupPath);
        if ($freeSpace !== false && $freeSpace < 104857600) { // 100MB
            throw new \Exception("Not enough disk space. Minimum 100MB required.");
        }

        $filename = "backup_{$type}_" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = $this->backupPath . '/' . $filename;
        
        $pdo = DB::connection()->getPdo();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        
        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \Exception("Could not create backup file.");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET time_zone = '+00:00';\n\n");

        foreach ($tables as $table) {
            $createTableQuery = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            fwrite($handle, "\n\nDROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createTableQuery['Create Table'] . ";\n\n");

            $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $chunkSize = 1000;

            for ($offset = 0; $offset < $count; $offset += $chunkSize) {
                $rows = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}")->fetchAll(\PDO::FETCH_ASSOC);
                if (empty($rows)) {
                    continue;
                }

                $columns = array_keys($rows[0]);
                $columnsStr = implode("`, `", $columns);

                foreach ($rows as $row) {
                    $values = array_map(function ($value) use ($pdo) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return $pdo->quote($value);
                    }, array_values($row));
                    
                    $valuesStr = implode(", ", $values);
                    fwrite($handle, "INSERT INTO `{$table}` (`{$columnsStr}`) VALUES ({$valuesStr});\n");
                }
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return $filePath;
    }

    public function createCodeBackup()
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "code_backup_{$timestamp}.zip";
        $filePath = $this->backupPath . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip archive.");
        }

        $basePath = base_path();
        $directoriesToZip = [
            'app',
            'config',
            'database/migrations',
            'routes',
            'resources/views',
            'public',
        ];

        $excludeNames = ['vendor', 'node_modules', '.git', '.env', 'storage/app/backups'];

        foreach ($directoriesToZip as $dir) {
            $dirPath = $basePath . '/' . $dir;
            if (!File::exists($dirPath)) continue;

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $realPath = $file->getRealPath();
                    $relativePath = substr($realPath, strlen($basePath) + 1);

                    // Skip excluded
                    $skip = false;
                    foreach ($excludeNames as $exclude) {
                        if (str_contains($relativePath, $exclude . '/') || $relativePath === $exclude) {
                            $skip = true;
                            break;
                        }
                    }

                    if (!$skip) {
                        $zip->addFile($realPath, $relativePath);
                    }
                }
            }
        }

        $zip->close();
        return $filePath;
    }

    public function createSettingsBackup()
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "settings_backup_{$timestamp}.json";
        $filePath = $this->backupPath . '/' . $filename;

        $settings = DB::table('settings')->get();
        File::put($filePath, $settings->toJson(JSON_PRETTY_PRINT));

        return $filePath;
    }

    public function restoreDatabaseBackup(string $filename)
    {
        $filePath = $this->backupPath . '/' . $filename;
        if (!File::exists($filePath)) {
            throw new \Exception("Backup file not found.");
        }

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $passwordArg = !empty($password) ? "-p\"{$password}\"" : "";
        $command = "mysql -h {$host} -P {$port} -u {$username} {$passwordArg} {$database} < \"{$filePath}\"";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Database restore failed.");
        }

        return true;
    }

    public function restoreSettingsBackup(string $filename)
    {
        $filePath = $this->backupPath . '/' . $filename;
        if (!File::exists($filePath)) {
            throw new \Exception("Settings backup file not found.");
        }

        $json = File::get($filePath);
        $settings = json_decode($json, true);

        if (!is_array($settings)) {
            throw new \Exception("Invalid settings file format.");
        }

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group'] ?? null] // Adjust columns based on actual settings table
            );
        }

        return true;
    }

    public function getLocalBackups()
    {
        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            $type = 'unknown';
            if (str_starts_with($name, 'backup_')) $type = 'database';
            elseif (str_starts_with($name, 'code_backup_')) $type = 'code';
            elseif (str_starts_with($name, 'settings_backup_')) $type = 'settings';

            $backups[] = [
                'name' => $name,
                'size' => $file->getSize(),
                'date' => $file->getMTime(),
                'type' => $type,
                'path' => $file->getPathname()
            ];
        }

        usort($backups, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return $backups;
    }

    public function cleanupOldBackups(int $daysToKeep = 30)
    {
        $files = File::files($this->backupPath);
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            $ext = $file->getExtension();
            if (in_array($ext, ['sql', 'zip', 'json'])) {
                if ($now - $file->getMTime() > ($daysToKeep * 86400)) {
                    File::delete($file->getPathname());
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    public function uploadToGoogleDrive(string $filePath, string $fileName)
    {
        $client = $this->getGoogleDriveClient();
        $service = new Google_Service_Drive($client);
        $folderId = $this->getGoogleDriveFolderId($service);

        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'parents' => [$folderId]
        ]);

        $content = file_get_contents($filePath);
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => File::mimeType($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        return $file->id;
    }

    public function downloadFromGoogleDrive(string $fileId, string $fileName)
    {
        $client = $this->getGoogleDriveClient();
        $service = new Google_Service_Drive($client);

        $response = $service->files->get($fileId, ['alt' => 'media']);
        $content = $response->getBody()->getContents();

        $filePath = $this->backupPath . '/' . $fileName;
        File::put($filePath, $content);

        return $filePath;
    }

    public function deleteFromGoogleDrive(string $fileId)
    {
        $client = $this->getGoogleDriveClient();
        $service = new Google_Service_Drive($client);
        
        try {
            $service->files->delete($fileId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function testGoogleDriveConnection()
    {
        try {
            $client = $this->getGoogleDriveClient();
            if ($client->isAccessTokenExpired()) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getGoogleDriveClient()
    {
        $client = new Google_Client();
        
        $clientId = DB::table('settings')->where('key', 'gdrive_client_id')->value('value');
        
        $clientSecretRaw = DB::table('settings')->where('key', 'gdrive_client_secret')->value('value');
        $clientSecret = $clientSecretRaw ? decrypt($clientSecretRaw) : null;
        
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setAccessType('offline');
        $client->addScope(Google_Service_Drive::DRIVE_FILE);

        $accessTokenRaw = DB::table('settings')->where('key', 'gdrive_access_token')->value('value');
        $accessToken = $accessTokenRaw ? decrypt($accessTokenRaw) : null;

        $refreshTokenRaw = DB::table('settings')->where('key', 'gdrive_refresh_token')->value('value');
        $refreshToken = $refreshTokenRaw ? decrypt($refreshTokenRaw) : null;

        if ($accessToken) {
            $token = [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 3600,
                'created' => time()
            ];
            $client->setAccessToken($token);
        }

        if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $newToken = $client->getAccessToken();
            
            if (isset($newToken['access_token'])) {
                DB::table('settings')->updateOrInsert(
                    ['key' => 'gdrive_access_token'],
                    ['value' => encrypt($newToken['access_token'])]
                );
            }
        }

        return $client;
    }

    public function getGoogleDriveFolderId($service = null)
    {
        if (!$service) {
            $client = $this->getGoogleDriveClient();
            $service = new Google_Service_Drive($client);
        }

        $cachedFolderId = DB::table('settings')->where('key', 'gdrive_folder_id')->value('value');
        if ($cachedFolderId) {
            try {
                // Verify folder exists
                $service->files->get($cachedFolderId);
                return $cachedFolderId;
            } catch (\Exception $e) {
                // Folder might be deleted, continue to create new one
            }
        }

        $folderName = DB::table('settings')->where('key', 'gdrive_folder_name')->value('value') ?? "App-Backups";
        
        $optParams = array(
            'q' => "mimeType='application/vnd.google-apps.folder' and name='{$folderName}' and trashed=false",
            'spaces' => 'drive',
            'fields' => 'files(id, name)'
        );
        $results = $service->files->listFiles($optParams);

        if (count($results->getFiles()) > 0) {
            $folderId = $results->getFiles()[0]->getId();
        } else {
            $folderMetadata = new Google_Service_Drive_DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);
            $folder = $service->files->create($folderMetadata, ['fields' => 'id']);
            $folderId = $folder->id;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'gdrive_folder_id'],
            ['value' => $folderId]
        );

        return $folderId;
    }

    public function getGoogleDriveBackups()
    {
        $client = $this->getGoogleDriveClient();
        $service = new Google_Service_Drive($client);
        $folderId = $this->getGoogleDriveFolderId($service);

        $optParams = array(
            'q' => "'{$folderId}' in parents and trashed=false",
            'spaces' => 'drive',
            'fields' => 'files(id, name, size, createdTime)',
            'orderBy' => 'createdTime desc'
        );

        try {
            $results = $service->files->listFiles($optParams);
            return $results->getFiles();
        } catch (\Exception $e) {
            return [];
        }
    }
}
