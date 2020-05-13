<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Transformers\{Data_MediaTransformer};

class MediaHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($model_type, $model, $collection_name = null)
    {
        $query = DB::connection('lfa__default')
                    ->table('media')
                    ->where('model_type', $model_type)
                    ->where('model_id', $model->id)
                    ->select(
                        'media.*'
                    );

        if ($collection_name) $query = $query->where('collection_name', $collection_name);

        return fractal($query->get()->toArray(), new Data_MediaTransformer())->toArray()['data'];
    }
}