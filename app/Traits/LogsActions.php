<?php

namespace App\Models\Traits;

use App\Models\ActionLogs;
use Illuminate\Database\Eloquent\Model;

trait LogsActions
{
    public static function bootLogsActions()
    {
        // Prevent logging the ActivityLog model itself
        if (static::class == ActionLogs::class) {
            return;
        }
        // Log creation and updates
        static::created(fn(Model $model) => $model->logActivity('created'));
        static::updated(fn(Model $model) => $model->logActivity('updated'));

        // Use "deleting" to capture model data before it is removed
        static::deleting(fn(Model $model) => $model->logActivity('deleted'));
    }

    protected function logActivity(string $action): void
    {
        $data = [
            'subject_type' => static::class,
            'subject_id'   => $this->getKey(),
            'user_id'      => auth()->id(),
            'ip_address'   => request()->ip(),
            'action'       => $action,
            'changes'      => $this->activityChanges($action),
        ];
        ActionLogs::create($data);
    }

    /**
     * Determine what data to store for each action
     */
    protected function activityChanges(string $action): ?array
    {
        if ($action == 'updated') {
            return [
                'before' => array_intersect_key($this->getOriginal(), $this->getDirty()),
                'after'  => $this->getChanges(),
            ];
        }

        if ($action == 'deleted') {
            // Store all original attributes before deletion
            return ['attributes' => $this->getOriginal()];
        }

        // No additional data for creation
        return null;
    }
}
