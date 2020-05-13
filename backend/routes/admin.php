<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes :: Administrative
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/**
 * Clients Management
 * -- API Resources Routes
**/
Route::prefix('associates')->group(function () {
    Route::get('/{associate_uuid}/clients', 'AdminAssociatesController@getClients')->name('associates.clients');
    Route::get('/{associate_uuid}/submissions', 'AdminAssociatesController@getSubmissions')->name('associates.submissions');
    Route::get('/{associate_uuid}/policies', 'AdminAssociatesController@getPolicies')->name('associates.policies');
    Route::get('/{associate_uuid}/logs', 'AdminAssociatesController@getActivityLogs')->name('associates.logs');
    //
    Route::post('/{associate_uuid}/send-welcome-email', 'AdminAssociatesController@sendWelcomeEmail')->name('associates.welcome');
    //
    Route::post('/{associate_uuid}/provider_codes', 'AdminAssociatesController@newProviderCodes')->name('associates.provider_codes.store');
    Route::delete('/{associate_uuid}/provider_codes/{pc_uuid}', 'AdminAssociatesController@deleteProviderCodes')->name('associates.provider_codes.delete');
    //
    Route::post('/{associate_uuid}/movements', 'AdminAssociatesController@newMovements')->name('associates.movements.store');
    Route::delete('/{associate_uuid}/movements/{movement_uuid}', 'AdminAssociatesController@deleteMovements')->name('associates.movements.delete');
    //
    Route::post('/{associate_uuid}/bandings_lfa', 'AdminAssociatesController@newBandingsLFA')->name('associates.bandings_lfa.store');
    Route::delete('/{associate_uuid}/bandings_lfa/{banding_uuid}', 'AdminAssociatesController@deleteBandingsLFA')->name('associates.bandings_lfa.delete');
    //
    Route::post('/{associate_uuid}/bandings_gi', 'AdminAssociatesController@newBandingsGI')->name('associates.bandings_gi.store');
    Route::delete('/{associate_uuid}/bandings_gi/{banding_uuid}', 'AdminAssociatesController@deleteBandingsGI')->name('associates.bandings_gi.delete');
});
Route::apiResource('associates', 'AdminAssociatesController')->parameters(['associates' => 'associate_uuid']);



/**
 * Clients Management
 * -- API Resources Routes
**/
Route::prefix('clients')->group(function () {
    Route::get('/{client_uuid}/submissions', 'AdminClientsController@getSubmissions')->name('clients.submissions');
    Route::get('/{client_uuid}/policies', 'AdminClientsController@getPolicies')->name('clients.policies');
    Route::get('/{client_uuid}/logs', 'AdminClientsController@getActivityLogs')->name('clients.logs');
    Route::get('/{client_uuid}/life-assured', 'AdminClientsController@getLifeAssured')->name('clients.life-assured');
});
Route::apiResource('clients', 'AdminClientsController')->parameters(['clients' => 'client_uuid']);


/**
 * Policies Management
 * -- API Resources Routes
**/
Route::apiResource('policies', 'AdminPoliciesController');

/**
 * Submissions Management
 * -- API Resources Routes
**/
Route::prefix('submissions')->group(function () {
    Route::get('/products', 'AdminSubmissionsController@getProducts')->name('submissions.products');
    //
    Route::get('/{submission_uuid}/cases', 'AdminSubmissionsController@getCases')->name('submissions.cases');
    Route::get('/{submission_uuid}/cases/{case_uuid}/documents/{media_id}', 'AdminSubmissionsController@getCaseMedia')->name('submissions.case.documents.download');
    //
    Route::get('/{submission_uuid}/logs', 'AdminSubmissionsController@getActivityLogs')->name('submissions.logs');
    //
    Route::get('/{submission_uuid}/media', 'AdminSubmissionsController@getMedia')->name('submissions.media.index');
    Route::get('/{submission_uuid}/media/{media_id}', 'AdminSubmissionsController@getSubmissionMedia')->name('submissions.media.download');
    //
    Route::post('/{submission_uuid}/restore', 'AdminSubmissionsController@restore')->name('submissions.restore');
    Route::post('/{submission_uuid}/status/{status_slug}', 'AdminSubmissionsController@updateStatus')->name('submissions.update.status');
});
Route::apiResource('submissions', 'AdminSubmissionsController')->parameters(['submissions' => 'submission_uuid']);
Route::get('portal/download/{case_uuid}/{file_type}', 'AdminSubmissionsController@downloadFromOldPortal')->name('submissions.old.download');








/**
 * User Accounts Management
 * -- API Resources Routes
**/
Route::apiResource('users', 'AdminUsersController');

/**
 * Products Management
 * -- API Resources Routes
**/
// Route::apiResource('providers', 'AdminProductsController');

/**
 * Payroll Management
 * -- API Resources Routes
**/
// Route::apiResource('providers', 'AdminPayrollController');
