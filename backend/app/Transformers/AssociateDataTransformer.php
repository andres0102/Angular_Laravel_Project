<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Helpers\Common;

class AssociateDataTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($associate)
    {
        $associate_array = Common::lfa_salesforce_data($associate);
        return $associate_array;
    }
}
