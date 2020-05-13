<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Helpers\Common;

class UserDataTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($user)
    {
        $associate_array = Common::lfa_staff_data($user);
        return $associate_array;
    }
}
