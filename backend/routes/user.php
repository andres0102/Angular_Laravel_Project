<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes :: Personnel
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('dashboard/notices', 'UserController@dashboardNotices');
Route::post('dashboard/notices/{notice_uuid}/read', 'UserController@readNotice');
Route::get('dashboard/notices/{notice_uuid}/comments', 'UserController@noticeGetComments');
Route::post('dashboard/notices/{notice_uuid}/comments', 'UserController@noticeAddComment');


// Route::post('dashboard/notices', 'UserController@dashboardNotices');

Route::post('dashboard/summary/associates', 'UserController@dashboardSummaryAssociates');
Route::post('dashboard/leaderboard/associates', 'UserController@dashboardLeaderboardAssociates');