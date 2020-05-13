<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Helpers\Common;

class Data_UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($user)
    {
      return array_merge([
          'uuid' => $user->uuid,
        ], Common::lfa_staff_data($user));
    }
}
