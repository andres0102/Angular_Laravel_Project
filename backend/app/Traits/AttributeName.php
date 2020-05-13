<?php

namespace App\Traits;

trait AttributeName
{
    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getNameAttribute()
    {
        return $this->name ??
               $this->full_name ??
               $this->title ??
               $this->product_name ??
               $this->component_name ??
               $this->product_code ??
               $this->component_code ??
               $this->product_type ??
               $this->transaction_desc ??
               $this->transaction_code ??
               $this->transaction_no;
    }
}
