<?php

namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

use App\Helpers\Common;
use App\Models\Media\Media;
use App\Transformers\Data_AuthorTransformer;

class NoticeDataTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($notice)
    {
        return [
            'uuid' => $notice->uuid,
            'title' => $notice->title,
            'details' => $notice->details,
            'location' => $notice->location,
            'full_day' => $notice->full_day,
            'start_date' => $notice->start_date,
            'start_time' => $notice->start_time,
            'end_date' => $notice->end_date,
            'end_time' => $notice->end_time,
            'created' => Carbon::parse($notice->created_at)->diffForHumans(),
            'read' => (boolean) $notice->user_read,
            'author' => fractal($notice->user_uuid, new Data_AuthorTransformer())->toArray()['data'],
            'comments_count' => $notice->comments_count
            // 'comments' => collect(json_decode($notice->comments))->transform(function ($comment, $key) {
            //     return [
            //         'uuid' => $comment->uuid,
            //         'content' => $comment->content,
            //         'author' => fractal($comment->user_uuid, new Data_AuthorTransformer())->toArray()['data'],
            //         'created_at' => $comment->created_at,
            //     ];
            // })
        ];
    }
}
