<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
|============
| Error Codes
|============
| 400 - Bad Request
| 401 - HTTP Unauthorized
| 402 - Payment Required
| 403 - Forbidden
| 404 - Not Found
| 405 - Method Not Allowed
| 500 - Internal Server Error
|
| // Application defined error codes
| 1000 - Invalid Email (Email does not exist)
| 1001 - Invalid Credentials
| 1002 - User has not verified email
*/


/**
 * General
**/
Route::post('status', function() { return response()->json(['message' => 'online']); });
Route::post('selections', 'PagesController@index')->name('selections');
Route::post('search', 'PagesController@search')->name('address.search');