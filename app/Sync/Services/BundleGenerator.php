<?php

namespace App\Sync\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BundleGenerator
{
    /**
     * Generates an incremental update bundle containing only changed files.
     */
    public function generateBundle(string $fromVersion, string $toVersion): array
    {
        $changedFiles = $this->detectChangedFiles($fromVersion);
        
        $manifest = [
            'from' => $fromVersion,
            'to' => $toVersion,
            'files' => [],
            'has_migrations' => $this->hasNewMigrations($fromVersion),
            'generated_at' => now(),
        ];

        foreach ($changedFiles as $file) {
            $path = $file->getRelativePathname();
            $manifest['files'][] = [
                'path' => $path,
                'hash' => md5_file($file->getPathname()),
                'url' => url("/updates/{$toVersion}/" . str_replace('\\', '/', $path))
            ];
        }

        return $manifest;
    }

    protected function detectChangedFiles(string $since): array
    {
        // In a real scenario, this would use Git or a file manifest comparison
        return File::allFiles(base_path('resources/views')); // Example: UI updates
    }

    protected function hasNewMigrations(string $since): bool
    {
        $migrations = File::files(database_path('migrations'));
        // Logic to check if any migration files are newer than the 'since' version
        return count($migrations) > 0; 
    }
}
