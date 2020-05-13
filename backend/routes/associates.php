<?php

use Illuminate\Http\Request;

use App\Helpers\AssociateHelper;

/*
|--------------------------------------------------------------------------
| API Routes :: Associates
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('associates', function() {
//   return AssociateHelper::index();
// });

/**
 * Submissions Management
 * -- API Resources Routes
**/
Route::prefix('submissions')->group(function () {
    Route::get('/products', 'AssociateSubmissionController@getProducts')->name('submissions.products');
    Route::get('/team', 'AssociateSubmissionController@teamIndex')->name('submissions.index.team');
    //
    Route::get('/{submission_uuid}/cases', 'AssociateSubmissionController@getCases')->name('submissions.cases');
    Route::post('/{submission_uuid}/cases', 'AssociateSubmissionController@addCases')->name('submissions.cases.add');
    Route::post('/{submission_uuid}/cases/{case_uuid}/documents/{collection_name}', 'AssociateSubmissionController@uploadMediaToCase')->name('submissions.case.documents.upload');
    Route::get('/{submission_uuid}/cases/{case_uuid}/documents/{media_id}', 'AssociateSubmissionController@getCaseMedia')->name('submissions.case.documents.download');
    Route::delete('/{submission_uuid}/cases/{case_uuid}/documents/{media_id}', 'AssociateSubmissionController@removeMediaFromCase')->name('submissions.case.documents.remove');
    Route::post('/{submission_uuid}/cases/{case_uuid}', 'AssociateSubmissionController@updateCases')->name('submissions.cases.update');
    Route::delete('/{submission_uuid}/cases/{case_uuid}', 'AssociateSubmissionController@deleteCases')->name('submissions.cases.delete');
    //
    Route::get('/{submission_uuid}/logs', 'AssociateSubmissionController@getActivityLogs')->name('submissions.logs');
    //
    Route::get('/{submission_uuid}/media', 'AssociateSubmissionController@getMedia')->name('submissions.media.index');
    Route::get('/{submission_uuid}/media/{media_id}', 'AssociateSubmissionController@getSubmissionMedia')->name('submissions.media.download');
    Route::post('/{submission_uuid}/media/{collection_name}', 'AssociateSubmissionController@uploadMedia')->name('submissions.media.upload');
    Route::delete('/{submission_uuid}/media/{media_id}', 'AssociateSubmissionController@removeMedia')->name('submissions.media.remove');
    //
    Route::post('/{submission_uuid}/begin-submission', 'AssociateSubmissionController@beginSubmission')->name('submissions.status.begin');
    Route::post('/{submission_uuid}/submit', 'AssociateSubmissionController@submit')->name('submissions.status.submit');
});
Route::apiResource('submissions', 'AssociateSubmissionController')->parameters(['submissions' => 'submission_uuid']);
Route::get('portal/download/{case_uuid}/{file_type}', 'AssociateSubmissionController@downloadFromOldPortal')->name('submissions.old.download');


/**
 * Teams Management
 * -- API Resources Routes
**/
Route::prefix('teams')->group(function () {
    Route::get('/salesforce/associates', 'AssociateTeamController@getSalesforceAssociates')->name('team.associates');
    Route::get('/salesforce/submission-report/{year?}/{month?}', 'AssociateTeamController@getSubmissionReport')->name('team.associates');
});



/**
 * Policies Management
 * -- API Resources Routes
**/
Route::prefix('policies')->group(function () {
    Route::get('/{policy_uuid}/transactions', 'AssociatePolicyController@transactions')->name('policies.transactions');
});
Route::apiResource('policies', 'AssociatePolicyController')->except(['destroy'])->parameters(['policies' => 'policy_uuid']);


/**
 * Clients Management
 * -- API Resources Routes
**/
Route::prefix('clients')->group(function () {
    Route::get('/{client_uuid}/submissions', 'AssociateClientController@getSubmissions')->name('clients.submissions');
    Route::get('/{client_uuid}/policies', 'AssociateClientController@getPolicies')->name('clients.policies');
    Route::get('/{client_uuid}/logs', 'AssociateClientController@getActivityLogs')->name('clients.logs');
    Route::get('/{client_uuid}/life-assured', 'AssociateClientController@getLifeAssured')->name('clients.life-assured');
    Route::get('/{client_uuid}/nominees', 'AssociateClientController@getNominees')->name('clients.nominees');
});
Route::post('/merge-clients', 'AssociateClientController@mergeClients')->name('clients.merge');
Route::apiResource('clients', 'AssociateClientController')->parameters(['clients' => 'client_uuid']);
Route::get('/full-clients', 'AssociateClientController@full_index')->name('clients.full.index');
// Leads
Route::apiResource('leads', 'AssociateLeadController')->parameters(['leads' => 'lead_uuid']);
Route::post('individuals/{individual_uuid}', 'AssociateLeadController@updateIndividual')->name('individual.update');


/**
 * Products Catalog
 * -- API Resources Routes
**/
Route::apiResource('products', 'AssociateProductController')->only(['index', 'show'])->parameters(['products' => 'product_uuid']);




