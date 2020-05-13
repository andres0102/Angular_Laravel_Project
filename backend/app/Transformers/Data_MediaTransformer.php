<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class Data_MediaTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($media)
    {
        if (strpos($media->mime_type, "image") === 0) $file_type = "image";
        else if ($media->mime_type === "application/pdf") $file_type = "pdf";
        else $file_type = "document";

        return [
            'id' => $media->id,
            'file_name' => $media->file_name,
            'collection_name' => $media->collection_name,
            'mime_type' => $media->mime_type,
            'file_type' => $file_type,
            'size' => $media->size,
            'created_at' => $media->created_at
        ];
    }
}
