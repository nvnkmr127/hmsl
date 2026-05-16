<?php

namespace App\Sync\Traits;

use Illuminate\Support\Str;

trait HasSyncMetadata
{
    public static function bootHasSyncMetadata(): void
    {
        static::creating(function ($model) {
            if (empty($model->sync_id)) {
                $model->sync_id = (string) Str::uuid();
            }
            $model->sync_version = 1;
            $model->sync_status = 'pending';
        });

        static::updating(function ($model) {
            if ($model->isDirty()) {
                $model->sync_version++;
                $model->sync_status = 'pending';
            }
        });
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }
}
