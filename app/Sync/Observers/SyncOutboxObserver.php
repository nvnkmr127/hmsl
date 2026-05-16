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
        // Only record if we are in local/offline mode
        if (Config::get('app.mode') !== 'local') {
            return;
        }

        SyncOutbox::create([
            'device_id' => Config::get('app.device_id'),
            'table_name' => $model->getTable(),
            'record_uuid' => $model->sync_id,
            'action' => $action,
            'payload' => $model->toArray(),
            'sync_version' => $model->sync_version,
            'status' => 'pending',
        ]);
    }
}
