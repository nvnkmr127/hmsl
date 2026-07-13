<?php

namespace App\Sync\Observers;

use App\Sync\Models\SyncOutbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class SyncOutboxObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'create');
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'update');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'delete');
    }

    protected function record(Model $model, string $action): void
    {
        // Only record if we are a client (have a sync token configured)
        if (!config('sync.token')) {
            return;
        }

        $syncId = $model->sync_id;

        // If sync_id is missing, try to assign one and persist it quietly
        if (empty($syncId)) {
            $syncId = (string) \Illuminate\Support\Str::uuid();
            try {
                \Illuminate\Support\Facades\DB::table($model->getTable())
                    ->where('id', $model->getKey())
                    ->update(['sync_id' => $syncId]);
            } catch (\Exception $e) {
                // Cannot assign sync_id — skip outbox entry to avoid NOT NULL constraint error
                return;
            }
        }

        SyncOutbox::create([
            'device_id'    => config('sync.device_id'),
            'table_name'   => $model->getTable(),
            'record_uuid'  => $syncId,
            'action'       => $action,
            'payload'      => $model->toArray(),
            'sync_version' => $model->sync_version ?? 1,
            'status'       => 'pending',
        ]);
    }
}
