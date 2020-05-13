<?php

namespace App\Traits;

trait AttributeCode
{
    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getCodeAttribute()
    {
        return $this->product_code ??
               $this->component_code ??
               $this->transaction_code ??
               $this->transaction_no;
    }
}