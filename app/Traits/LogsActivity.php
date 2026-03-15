<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait to hook into model events.
     */
    protected static function bootLogsActivity()
    {
        // Don't log if we're running from console/seeders without an active request
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        static::created(function (Model $model) {
            $model->logActivity('created', $model->getLogDescription('created'), $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            // Don't log if the only change was the 'updated_at' column
            if (count($changes) === 1 && array_key_exists('updated_at', $changes)) {
                return;
            }

            $model->logActivity('updated', $model->getLogDescription('updated'), [
                'old' => array_intersect_key($model->getOriginal(), $changes),
                'new' => $changes,
            ]);
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted', $model->getLogDescription('deleted'), $model->getOriginal());
        });
    }

    /**
     * Helper to insert the log row.
     */
    public function logActivity(string $action, string $description, ?array $properties = null)
    {
        // Default to auth()->id() if user is authenticated (standard login context)
        // If not, allow custom contextual override (eg POS sessions)
        $userId = auth()->id() ?? session('pos_admin_id');

        // If there's truly no user context, we skip logging to avoid errors
        // (This happens during testing or setup when no user is logged in yet)
        if (!$userId) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Default description generation. Models can override this.
     */
    protected function getLogDescription(string $action): string
    {
        $className = class_basename(get_class($this));
        $name = $this->name ?? $this->id; // Fallback to ID if no name property

        return "{$action} {$className}: {$name}";
    }
}