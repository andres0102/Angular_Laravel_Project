<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class Data_ActivityLogTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($log)
    {
        return [
            'uuid' => $log->uuid,
            'event' => $log->event,
            'title' => $log->title,
            'old_data' => $log->old_data ? json_decode($log->old_data) : null,
            'new_data' => $log->new_data ? json_decode($log->new_data) : null,
            'user' => $log->user_uuid ? [
                'uuid' => $log->user_uuid,
                'name' => $log->user_name,
                'gender' => $log->user_gender
            ] : null,
            'target_model' => $log->target_model,
            'target_uuid' => $log->target_uuid,
            'created_at' => $log->created_at
        ];
    }
}
