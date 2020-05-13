<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Transformers\{NoticeDataTransformer, Data_CommentTransformer};

class NoticeHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($notice = null)
    {
        // $comments_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
        //                     'uuid', uuid,
        //                     'content', content,
        //                     'created_at', created_at,
        //                     'user_uuid', user_uuid
        //                 )) FROM lfa__general.comments WHERE commentable_id = notice.id and commentable_type = 'notices' ORDER BY created_at DESC) as comments";

        $query = DB::connection('lfa__general')
                    ->table('notices as notice')
                    ->leftJoin('lfa__general.logs as read_log', function($join)
                      {
                        $join->on('read_log.user_uuid', '=', DB::raw("'".(auth()->user()->uuid ?? 0) ."'"));
                        $join->on('read_log.loggable_id', '=', 'notice.id');
                        $join->on('read_log.loggable_type', '=', DB::raw("'notices'"));
                        $join->on('read_log.event', '=', DB::raw("'read'"));
                      })
                    ->select('notice.*',
                             DB::raw('ifnull(read_log.id, null) as user_read'),
                             DB::raw('(SELECT count(*) FROM lfa__general.comments WHERE commentable_id = notice.id and commentable_type = "notices") as comments_count')
                           )->where('important', true);

        if ($notice) {
            $results = $query->where('notice.uuid', $notice->uuid);
            return fractal($results->first(), new NoticeDataTransformer())->toArray()['data'];
        } else {
            return fractal($query->latest()->paginate(3), new NoticeDataTransformer());
        }
    }


    public static function comments($notice, $comment = null)
    {
        $query = DB::connection('lfa__general')
                    ->table('comments')
                    ->select('*')
                    ->where('commentable_type', 'notices')
                    ->where('commentable_id', $notice->id);

        if ($comment) {
            $results = $query->where('uuid', $comment->uuid);
            return fractal($results->first(), new Data_CommentTransformer())->toArray()['data'];
        } else {
            return fractal($query->latest()->paginate(5), new Data_CommentTransformer());
        }
    }
}