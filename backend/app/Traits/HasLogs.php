<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\General\ActivityLog;
use App\Helpers\Common;

trait HasLogs
{
    /** ===================================================================================================
     * The name of the model.
     *
     * @return string
     */
    public function loggableModel(): string
    {
        return ActivityLog::class;
    }

    /** ===================================================================================================
     * The logs attached to the model.
     *
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany($this->loggableModel(), 'loggable');
    }

    /** ===================================================================================================
     * The logs attached to the model.
     *
     * @return MorphMany
     */
    public function log($user = null, $event, $title, $old_data = null, $new_data = null, $target_model = null, $target_uuid = null, $timestamp = null)
    {
        switch ($event) {
            case 'created':
                $old_data = null;
                $new_data = $this;
                $timestamp = $this->created_at;
                break;
        }

        $log_array = [
            'user_uuid' => ($user) ? $user->uuid : null,
            'event' => $event,
            'title' => $title,
            'old_data' => Common::validData($old_data) ? json_encode($old_data) : null,
            'new_data' => Common::validData($new_data) ? json_encode($new_data) : null,
            'target_model' => $target_model,
            'target_uuid' => $target_uuid,
        ];

        if ($timestamp) {
            $log_array = array_merge($log_array, [
                'created_at' => $timestamp
            ]);
        }

        return $this->logs()->create($log_array);
    }

}
