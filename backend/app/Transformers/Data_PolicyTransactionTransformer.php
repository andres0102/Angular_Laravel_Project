<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class Data_PolicyTransactionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($transaction)
    {
        return [
            'uuid' => $transaction->uuid,
            'year' => $transaction->year,
            'month' => $transaction->month,
            'transaction_no' => $transaction->transaction_no,
            'transaction_code' => $transaction->transaction_code,
            'transaction_desc' => $transaction->transaction_desc,
            'date_transaction' => $transaction->date_transaction,
            'date_instalment_from' => $transaction->date_instalment_from,
            'date_instalment_to' => $transaction->date_instalment_to,
            'date_due' => $transaction->date_due,
            'product_code' => $transaction->product_code,
            'product_type' => $transaction->product_type,
            'product_name' => $transaction->product_name,
            'component_code' => $transaction->component_code,
            'component_name' => $transaction->component_name,
            'payment_currency' => $transaction->payment_currency,
            'premium' => $transaction->premium,
            'premium_gst' => $transaction->premium_gst,
            'premium_loading' => $transaction->premium_loading,
            'premium_conversion_rate' => $transaction->premium_conversion_rate,
            'premium_type' => $transaction->premium_type,
        ];
    }
}
