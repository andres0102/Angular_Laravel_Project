<?php

namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

use App\Transformers\Data_AuthorTransformer;

class Data_CommentTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($comment)
    {
        return [
            'uuid' => $comment->uuid,
            'content' => $comment->content,
            'author' => fractal($comment->user_uuid, new Data_AuthorTransformer())->toArray()['data'],
            'created_at' => $comment->created_at,
        ];
    }
}
