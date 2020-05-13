<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\General\Comment;
use App\Helpers\Common;

trait HasComments
{
    /** ===================================================================================================
     * The name of the model.
     *
     * @return string
     */
    public function commentableModel(): string
    {
        return Comment::class;
    }

    /** ===================================================================================================
     * The comments attached to the model.
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany($this->commentableModel(), 'commentable');
    }

    /** ===================================================================================================
     * The logs attached to the model.
     *
     * @return MorphMany
     */
    public function comment($content)
    {
        return $this->comments()->create([
            'user_uuid' => (auth()->user()) ? auth()->user()->uuid : null,
            'content' => $content,
        ]);
    }

}
