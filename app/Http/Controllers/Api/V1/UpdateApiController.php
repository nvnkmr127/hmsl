<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UpdateApiController extends Controller
{
    // Files/directories to include in code sync
    protected array $syncPaths = [
        'resources/views',
        'resources/css',
        'resources/js',
        'public/build',
        'public/js',
        'public/css',
        'public/fonts',
    ];

    /**
     * Check if an update is available.
     * Client sends its current APP_VERSION, server responds with whether an update exists.
     */
    public function check(Request $request)
    {
        $clientVersion = $request->query('current_version', '0.0.0');
        $serverVersion = config('app.version', env('APP_VERSION', '1.0.0'));

        $hasUpdate = version_compare($clientVersion, $serverVersion, '<');

        return response()->json([
            'has_update' => $hasUpdate,
            'server_version' => $serverVersion,
            'client_version' => $clientVersion,
        ]);
    }

    /**
     * Return a manifest of all syncable files with their hashes.
     * Client compares this to local file hashes to know what changed.
     */
    public function manifest(Request $request)
    {
        $manifest = [];

        foreach ($this->syncPaths as $relPath) {
            $absPath = base_path($relPath);
            if (!File::exists($absPath)) continue;

            if (File::isDirectory($absPath)) {
                $files = File::allFiles($absPath);
                foreach ($files as $file) {
                    $relativePath = ltrim(str_replace(base_path(), '', $file->getRealPath()), DIRECTORY_SEPARATOR);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $manifest[$relativePath] = md5_file($file->getRealPath());
                }
            } else {
                $relativePath = ltrim(str_replace(base_path(), '', $absPath), DIRECTORY_SEPARATOR);
                $relativePath = str_replace('\\', '/', $relativePath);
                $manifest[$relativePath] = md5_file($absPath);
            }
        }

        return response()->json([
            'version' => config('app.version', env('APP_VERSION', '1.0.0')),
            'files' => $manifest,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Serve a single file's content for download by the offline client.
     * Path is passed as a query param, validated to stay within allowed directories.
     */
    public function download(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            return response()->json(['error' => 'path is required'], 422);
        }

        // Security: normalize and validate path stays within allowed dirs
        $normalized = str_replace(['..', '\\'], ['', '/'], $path);
        $absPath = base_path($normalized);

        $allowed = false;
        foreach ($this->syncPaths as $syncPath) {
            if (str_starts_with($normalized, $syncPath)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed || !File::exists($absPath) || File::isDirectory($absPath)) {
            return response()->json(['error' => 'File not found or not allowed'], 404);
        }

        return response()->file($absPath);
    }
}
