<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillSyncIds extends Command
{
    protected $signature = 'sync:backfill-ids';
    protected $description = 'Generate sync_ids for all existing records that are missing them.';

    public function handle()
    {
        $tables = config('sync.tables', []);
        
        $this->info("Starting backfill of sync_ids for existing data...");

        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $this->warn("Skipping table '$table' (does not exist).");
                continue;
            }

            if (!DB::getSchemaBuilder()->hasColumn($table, 'sync_id')) {
                $this->warn("Skipping table '$table' (no sync_id column).");
                continue;
            }

            $records = DB::table($table)->whereNull('sync_id')->get();
            $count = $records->count();

            if ($count === 0) {
                $this->info("Table '$table' is already fully populated.");
                continue;
            }

            $this->info("Updating $count records in '$table'...");

            $updated = 0;
            foreach ($records as $record) {
                $uuid = (string) Str::uuid();
                DB::table($table)->where('id', $record->id)->update([
                    'sync_id' => $uuid,
                    'sync_version' => 1,
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);
                $updated++;
            }

            $this->info("Successfully updated $updated records in '$table'.");
        }

        $this->info("Backfill complete! Your existing web data is now ready to be pulled.");
        
        return 0;
    }
}
