<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            ['key' => 'auto_backup', 'value' => 'false', 'group' => 'backup'],
            ['key' => 'backup_frequency', 'value' => 'daily', 'group' => 'backup'],
            ['key' => 'backup_retention_days', 'value' => '30', 'group' => 'backup'],
            ['key' => 'auto_cleanup', 'value' => 'true', 'group' => 'backup'],
            ['key' => 'backup_notifications', 'value' => 'false', 'group' => 'backup'],
            ['key' => 'notification_email', 'value' => '', 'group' => 'backup'],
            ['key' => 'backup_gdrive_enabled', 'value' => 'false', 'group' => 'backup'],
            ['key' => 'gdrive_client_id', 'value' => '', 'group' => 'backup'],
            ['key' => 'gdrive_client_secret', 'value' => '', 'group' => 'backup'],
            ['key' => 'gdrive_folder_name', 'value' => 'App-Backups', 'group' => 'backup'],
            ['key' => 'gdrive_access_token', 'value' => '', 'group' => 'backup'],
            ['key' => 'gdrive_refresh_token', 'value' => '', 'group' => 'backup'],
            ['key' => 'google_drive_folder_id', 'value' => '', 'group' => 'backup'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'auto_backup',
            'backup_frequency',
            'backup_retention_days',
            'auto_cleanup',
            'backup_notifications',
            'notification_email',
            'backup_gdrive_enabled',
            'gdrive_client_id',
            'gdrive_client_secret',
            'gdrive_folder_name',
            'gdrive_access_token',
            'gdrive_refresh_token',
            'google_drive_folder_id',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
