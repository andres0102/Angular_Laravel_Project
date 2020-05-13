<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Transformers\{Data_ActivityLogTransformer};

class ActivityLogHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($loggable_type, $loggable_model)
    {
        $query = DB::connection('lfa__general')
                    ->table('logs as log')
                    ->where('loggable_type', $loggable_type)
                    ->where('loggable_id', $loggable_model->id)
                    ->select(
                        'log.*',
                        DB::raw("(SELECT full_name from lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_users.users WHERE uuid = log.user_uuid)) as user_name"),
                        DB::raw("(SELECT gender_slug from lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_users.users WHERE uuid = log.user_uuid)) as user_gender")
                    )->orderByDesc('log.created_at');

        return fractal($query->get()->toArray(), new Data_ActivityLogTransformer())->toArray()['data'];
    }
}