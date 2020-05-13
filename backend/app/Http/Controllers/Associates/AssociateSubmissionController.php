<?php

namespace App\Http\Controllers\Associates;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Illuminate\Support\Facades\Storage;

use App\Helpers\{SubmissionHelper, ActivityLogHelper, SubmissionCaseHelper, ClientHelper, IndividualHelper, MediaHelper};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\{Client, LifeAssured};
use App\Models\LegacyFA\Products\{Product, ProductOption, Rider};
use App\Models\LegacyFA\Submissions\{Submission, SubmissionCase};
use App\Models\Selections\LegacyFA\{SelectSubmissionCategory, SelectPaymentMode, SelectProvider, SelectProductCategory, SelectSubmissionStatus};

class AssociateSubmissionController extends Controller
{
    /**
     * Create a new AssociateController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        $this->middleware('auth');
    }


    /** ===================================================================================================
     * Get Associate's Submissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            return response()->json([
                'error' => false,
                'data' => SubmissionHelper::index(null, $sales_associate)
            ]);
          } else {
            // Forbidden HTTP Request
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get Associate's Team Submissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function teamIndex()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_teams_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            return response()->json([
                'error' => false,
                'data' => SubmissionHelper::index(null, null, null, null, $sales_associate->active_sales_agents()->pluck('uuid'))
            ]);
          } else {
            // Forbidden HTTP Request
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LegacyFA\Associates\Associate  $associate
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
            if ($user->is_associate && $sales_associate = $user->sales_associate && $submission = Submission::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionHelper::index($submission)
                ]);
            } else {
                return Common::reject(404, 'submission_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Store into database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_create')) {
            if ($user->is_associate && $sales_associate = $user->sales_associate) {
                request()->validate([
                    'client_uuid' => 'required|uuid|exists:lfa_clients.clients,uuid',
                    'updates' => 'nullable',
                    'identity' => 'nullable',
                    'pfr' => 'nullable',
                    'poa' => 'nullable',
                    'sc' => 'nullable',
                    'others' => 'nullable',
                ]);

                $client_uuid = request()->input('client_uuid');
                if ($client = Client::firstUuid($client_uuid)) {

                    if ($client->associate->is($sales_associate)) {
                        $old_data = $client->fresh();

                        $updates = collect(json_decode(request()->input('updates')));

                        // Update Client Display Name
                        ClientHelper::update($updates->only(ClientHelper::fields('display_name')), $client, 'display_name');
                        // Update Client Record
                        ClientHelper::update($updates->only(ClientHelper::fields('client')), $client, 'client');

                        $client_type_slug = $updates['client_type_slug'] ?? $client->client_type_slug;
                        if ($client_type_slug == 'individual') {
                            if (!$individual = $client->individual) {
                                // Individual does not exists
                                // Lets create a new indivdual
                                $individual = IndividualHelper::create([
                                    'full_name' => Common::trimStringUpper($updates['display_name'] ?? $updates['full_name'] ?? null),
                                    'nric_no' => Common::trimStringUpper($updates['nric_no'] ?? null),
                                ]);
                            }

                            // Update Client Individual Record
                            ClientHelper::update($updates->only(ClientHelper::fields('individual')), $individual, 'individual');
                            // Update Client Individual :: Contact
                            ClientHelper::update($updates->only(ClientHelper::fields('individual_contact')), $individual, 'individual_contact');
                            // Update Client Individual :: Address
                            ClientHelper::update($updates->only(ClientHelper::fields('individual_address')), $individual, 'individual_address');
                        }

                        $client->log($user, 'client_updated', 'Client record updated.', $old_data, $client->fresh(), 'clients', $client->uuid);

                        // Create new Submission record under Client
                        $policy_holder = $client->fresh();
                        $p_h_i = $policy_holder->individual;
                        $p_h_i_contact = $p_h_i->contacts()->where('contact_type_slug', 'default')->first();
                        $p_h_i_address = $p_h_i->addresses()->where('address_type_slug', 'residential')->first();

                        $submission = $sales_associate->submissions()->create([
                            'status_slug' => 'draft',
                            'client_uuid' => $policy_holder->uuid,
                            'date_submission' => Carbon::now()->format('Y-m-d'),
                            'client_type' => $policy_holder->type->title,
                            'client_name' => strtoupper($policy_holder->display_name),
                            'client_business_name' => $policy_holder->business_name,
                            'client_business_uen' => $policy_holder->business_uen,
                            'client_description' => $policy_holder->description,
                            'associate_name' => strtoupper($sales_associate->name),
                            'supervisor_name' => ($sales_associate->direct_supervisor) ? strtoupper($sales_associate->direct_supervisor->name) : null,
                            'client_personal' => ($p_h_i) ? [
                              'salutation_slug' => $p_h_i->salutation_slug ?? null,
                              'full_name' => $p_h_i->full_name ?? null,
                              'alias' => $p_h_i->alias ?? null,
                              'chinese_name' => $p_h_i->chinese_name ?? null,
                              'nric_no' => $p_h_i->nric_no ?? null,
                              'fin_no' => $p_h_i->fin_no ?? null,
                              'passport_no' => $p_h_i->passport_no ?? null,
                              'gender_slug' => $p_h_i->gender_slug ?? null,
                              'date_birth' => $p_h_i->date_birth ?? null,
                              'race_slug' => $p_h_i->race_slug ?? null,
                              'country_birth_slug' => $p_h_i->country_birth_slug ?? null,
                              'nationality_slug' => $p_h_i->nationality_slug ?? null,
                              'residency_status_slug' => $p_h_i->residency_status_slug ?? null,
                              'marital_status_slug' => $p_h_i->marital_status_slug ?? null,
                              'employment_status_slug' => $p_h_i->employment_status_slug ?? null,
                              'income_range' => $p_h_i->income_range ?? null,
                              'job_title' => $p_h_i->job_title ?? null,
                              'company_name' => $p_h_i->company_name ?? null,
                              'business_nature' => $p_h_i->business_nature ?? null,
                              'education_level_slug' => $p_h_i->education_level_slug ?? null,
                              'education_institution' => $p_h_i->education_institution ?? null,
                              'field_of_study' => $p_h_i->field_of_study ?? null,
                              'smoker' => $p_h_i->smoker ?? null,
                              'selected' => $p_h_i->selected ?? null,
                              'pdpa' => $p_h_i->pdpa ?? null,
                              'contact_information' => [
                                'home_no' => ($p_h_i_contact) ? $p_h_i_contact->home_no : null,
                                'mobile_no' => ($p_h_i_contact) ? $p_h_i_contact->mobile_no : null,
                                'fax_no' => ($p_h_i_contact) ? $p_h_i_contact->fax_no : null,
                                'email' => ($p_h_i_contact) ? $p_h_i_contact->email : null,
                              ],
                              'address_information' => [
                                'block' => ($p_h_i_address) ? $p_h_i_address->block : null,
                                'street' => ($p_h_i_address) ? $p_h_i_address->street : null,
                                'unit' => ($p_h_i_address) ? $p_h_i_address->unit : null,
                                'building' => ($p_h_i_address) ? $p_h_i_address->building : null,
                                'city' => ($p_h_i_address) ? $p_h_i_address->city : null,
                                'postal' => ($p_h_i_address) ? $p_h_i_address->postal : null,
                                'country_slug' => ($p_h_i_address) ? $p_h_i_address->country_slug : null,
                              ]
                            ] : null,
                        ]);

                        if (request()->has('identity')) {
                            foreach(request()->file('identity') as $file) {
                                $submission->addMedia($file)->toMediaCollection('client-identity');
                            }
                        }

                        if (request()->has('pfr')) {
                            foreach(request()->file('pfr') as $file) {
                                $submission->addMedia($file)->toMediaCollection('pfr');
                            }
                        }

                        if (request()->has('poa')) {
                            foreach(request()->file('poa') as $file) {
                                $submission->addMedia($file)->toMediaCollection('proof-of-address');
                            }
                        }

                        if (request()->has('sc')) {
                            foreach(request()->file('sc') as $file) {
                                $submission->addMedia($file)->toMediaCollection('submission-checklist');
                            }
                        }

                        if (request()->has('others')) {
                            foreach(request()->file('others') as $file) {
                                $submission->addMedia($file)->toMediaCollection('other-documents');
                            }
                        }

                        return response()->json([
                            'error' => false,
                            'data' => SubmissionHelper::index($submission)
                        ]);
                    } else {
                        return Common::reject(401, 'unauthorized_associate_for_client_uuid');
                    }
                } else {
                  return Common::reject(404, 'client_not_found');
                }

            // request()->validate(ClientHelper::validations(null, true));

            // if (request()->input('new') !== true && $sales_associate->findClient(strtoupper(request()->input('display_name')), strtoupper(request()->input('nric_no')))) {
            //     return response()->json([
            //         'error' => true,
            //         'message' => 'client_exists'
            //     ]);
            // }

            // return response()->json([
            //     'error' => false,
            //     'data' => ClientHelper::create(request()->only(ClientHelper::fields()), $sales_associate)
            // ]);
            } else {
                // Forbidden HTTP Request
                return Common::reject(403, 'user_is_not_associate');
            }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Store into database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCases($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_create')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {

                request()->validate([
                    'submission' => 'required',
                    'case' => 'required',
                    'files_app' => 'nullable',
                    'files_bi' => 'nullable',
                    'files_giro' => 'nullable',
                    'files_medi' => 'nullable',
                    'files_supp' => 'nullable',
                    'files_doc' => 'nullable',
                ]);

                $submission_data = collect(json_decode(request()->input('submission')));
                $case_data = collect(json_decode(request()->input('case')));
                $riders_data = collect(json_decode(request()->input('riders')));

                $client = $submission->policy_holder;
                $life_assured = null;
                $product_option = null;
                $product = null;
                $product_cat = null;
                $provider = null;

                $submission_cat = SelectSubmissionCategory::firstSlug($submission_data['submission_cat_slug']);

                if ($submission_data['submission_cat_slug'] == 'insurance') {
                    // Lets settle Life Assured first to get life_assured_uuid
                    if ($case_data['life_assured_is_client'] === false && $case_data['life_assured_type'] == "individual" && $case_data['life_assured_uuid']){
                      // Life assured exists
                      $life_assured = LifeAssured::firstUuid($case_data['life_assured_uuid']);
                    } else if ($case_data['life_assured_is_client'] === false && $case_data['life_assured_type'] == "individual" && $case_data['life_assured']) {
                      // Life assured does not exists, lets create one record now
                      $display_name = Common::trimStringUpper($case_data['life_assured']);
                      $life_assured = $client->findOrNewLifeAssured($display_name, ($case_data['nric_no'] ?? $case_data['fin_no'] ?? $case_data['passport_no'] ?? null));
                      if ($life_assured->is($client)) $case_data['life_assured_is_client'] = true;
                    }

                    // Update Life Assured Record
                    if ($case_data['relationship_type_slug']) $life_assured->update(['relationship_type_slug' => $case_data['relationship_type_slug']]);

                    if ($life_assured) {
                      // Get Individual Reference
                      $life_assured_individual = $life_assured->individual;
                      // Update Client Individual Record
                      ClientHelper::update($case_data->only(ClientHelper::fields('individual')), $life_assured_individual, 'individual');
                      // Update Client Individual :: Contact
                      ClientHelper::update($case_data->only(ClientHelper::fields('individual_contact')), $life_assured_individual, 'individual_contact');
                      // Update Client Individual :: Address
                      ClientHelper::update($case_data->only(ClientHelper::fields('individual_address')), $life_assured_individual, 'individual_address');

                      $life_assured_individual_contact = $life_assured_individual->contacts()->where('contact_type_slug', 'default')->first();
                      $life_assured_individual_address = $life_assured_individual->addresses()->where('address_type_slug', 'residential')->first();
                    }

                    // Retrieve product info
                    $product_option = ProductOption::firstUuid($case_data['product_option_uuid']);
                    $product = $product_option->product;
                    $product_cat = $product_option->category;
                    $provider = $product->provider;
                } else if ($submission_data['submission_cat_slug'] == 'cis') {
                    // Retrieve product info
                    $product_option = ProductOption::firstUuid($case_data['product_option_uuid']);
                    $product = $product_option->product;
                    $product_cat = $product_option->category;
                    $provider = $product->provider;
                } else if ($submission_data['submission_cat_slug'] == 'gi') {
                    // Retrieve product info
                    $provider = SelectProvider::where('slug', $case_data['provider_slug'])->first();
                    $product_cat = SelectProductCategory::where('slug', $case_data['product_cat_slug'])->first();
                } else if ($submission_data['submission_cat_slug'] == 'wills') {
                    // Retrieve product info
                    $provider = SelectProvider::where('slug', $case_data['provider_slug'])->first();
                }

                $payment_mode = ($case_data['payment_mode_slug']) ? SelectPaymentMode::firstSlug($case_data['payment_mode_slug']) : null;

                // Create Case Data
                $case = $submission->cases()->create([
                  'submission_uuid' => $submission->uuid,
                  'associate_uuid' => $sales_associate->uuid,
                  //
                  'client_uuid' => $client->uuid ?? null,
                  'life_assured_uuid' => (Common::validData($life_assured) && !$case_data['life_assured_is_client']) ? $life_assured->uuid : null,
                  'provider_slug' => (Common::validData($case_data, 'provider_slug')) ? $case_data['provider_slug'] : null,
                  'submission_cat_slug' => $submission_data['submission_cat_slug'] ?? null,
                  'product_cat_slug' => ($product_cat) ? $product_cat->slug : null,
                  'product_uuid' => (Common::validData($product)) ? $product->uuid : null,
                  'product_option_uuid' => (Common::validData($product_option)) ? $product_option->uuid : null,
                  // Snapshot Values
                  'currency' => (Common::validData($case_data, 'currency')) ? $case_data['currency'] : 'sgd',
                  'ape' => (float) (Common::validData($case_data, 'ape')) ? $case_data['ape'] : 0,
                  'provider_name' => ($provider) ? $provider->full_name : null,
                  'life_assured_name' => ($life_assured && $life_assured_individual && !$case_data['life_assured_is_client']) ? $life_assured_individual->full_name : null,
                  'life_assured_is_client' => ($case_data['life_assured_is_client']) ? true : false,
                  // Life Assured Individual Record
                  'life_assured_personal' => ($life_assured && Common::validData($life_assured_individual) && !$case_data['life_assured_is_client']) ? [
                    'salutation_slug' => $life_assured_individual->salutation_slug ?? null,
                    'full_name' => $life_assured_individual->full_name ?? null,
                    'alias' => $life_assured_individual->alias ?? null,
                    'chinese_name' => $life_assured_individual->chinese_name ?? null,
                    'nric_no' => $life_assured_individual->nric_no ?? null,
                    'fin_no' => $life_assured_individual->fin_no ?? null,
                    'passport_no' => $life_assured_individual->passport_no ?? null,
                    'gender_slug' => $life_assured_individual->gender_slug ?? null,
                    'date_birth' => $life_assured_individual->date_birth ?? null,
                    'race_slug' => $life_assured_individual->race_slug ?? null,
                    'country_birth_slug' => $life_assured_individual->country_birth_slug ?? null,
                    'nationality_slug' => $life_assured_individual->nationality_slug ?? null,
                    'residency_status_slug' => $life_assured_individual->residency_status_slug ?? null,
                    'marital_status_slug' => $life_assured_individual->marital_status_slug ?? null,
                    'employment_status_slug' => $life_assured_individual->employment_status_slug ?? null,
                    'income_range' => $life_assured_individual->income_range ?? null,
                    'job_title' => $life_assured_individual->job_title ?? null,
                    'company_name' => $life_assured_individual->company_name ?? null,
                    'business_nature' => $life_assured_individual->business_nature ?? null,
                    'education_level_slug' => $life_assured_individual->education_level_slug ?? null,
                    'education_institution' => $life_assured_individual->education_institution ?? null,
                    'field_of_study' => $life_assured_individual->field_of_study ?? null,
                    'smoker' => $life_assured_individual->smoker ?? null,
                    'selected' => $life_assured_individual->selected ?? null,
                    'pdpa' => $life_assured_individual->pdpa ?? null,
                    'contact_information' => [
                      'home_no' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->home_no : null,
                      'mobile_no' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->mobile_no : null,
                      'fax_no' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->fax_no : null,
                      'email' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->email : null,
                    ],
                    'address_information' => [
                      'block' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->block : null,
                      'street' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->street : null,
                      'unit' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->unit : null,
                      'building' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->building : null,
                      'city' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->city : null,
                      'postal' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->postal : null,
                      'country_slug' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->country_slug : null,
                    ]
                  ] : null,
                  'submission_category' => $submission_cat->title ?? null,
                  'product_category' => ($product_cat) ? $product_cat->title : null,
                  'product_name' => ($product) ? $product->name : null,
                  'option_name' => ($product_option) ? $product_option->name : null,
                  'investment_account_type' => (Common::validData($case_data, 'investment_account_type')) ? $case_data['investment_account_type'] : null,
                  'investment_transaction_type' => (Common::validData($case_data, 'investment_transaction_type')) ? $case_data['investment_transaction_type'] : null,
                  'loan_property_address' => (Common::validData($case_data, 'loan_property_address')) ? $case_data['loan_property_address'] : null,
                  'loan_amount' => (float) (Common::validData($case_data, 'loan_amount')) ? $case_data['loan_amount'] : 0,
                  'loan_platform' => (float) (Common::validData($case_data, 'loan_platform')) ? $case_data['loan_platform'] : null,
                  'loan_bank_slug' => (float) (Common::validData($case_data, 'loan_bank_slug')) ? $case_data['loan_bank_slug'] : null,
                  'loan_interest_rate' => (float) (Common::validData($case_data, 'loan_interest_rate')) ? $case_data['loan_interest_rate'] : 0,
                  'policy_term' => (float) (Common::validData($case_data, 'policy_term')) ? $case_data['policy_term'] : 0,
                  'sum_assured' => (float) (Common::validData($case_data, 'sum_assured')) ? $case_data['sum_assured'] : 0,
                  'payment_term' => (float) (Common::validData($case_data, 'payment_term')) ? $case_data['payment_term'] : 0,
                  'payment_frequency' => (Common::validData($case_data, 'payment_frequency')) ? $case_data['payment_frequency'] : 0,
                  'payment_type' => (Common::validData($case_data, 'payment_type')) ? $case_data['payment_type'] : null,
                  'gst_rate' => (float) (Common::validData($case_data, 'gst_rate')) ? $case_data['gst_rate'] : 0,
                  'gross_payment_before_gst' => (float) (Common::validData($case_data, 'gross_payment_before_gst')) ? $case_data['gross_payment_before_gst'] : 0,
                  'gross_payment_gst' => (float) (Common::validData($case_data, 'gross_payment_gst')) ? $case_data['gross_payment_gst'] : 0,
                  'gross_payment_after_gst' => (float) (Common::validData($case_data, 'gross_payment_after_gst')) ? $case_data['gross_payment_after_gst'] : 0,
                  'payment_discount' => (float) (Common::validData($case_data, 'payment_discount')) ? $case_data['payment_discount'] : 0,
                  'nett_payment_before_gst' => (float) (Common::validData($case_data, 'nett_payment_before_gst')) ? $case_data['nett_payment_before_gst'] : 0,
                  'nett_payment_gst' => (float) (Common::validData($case_data, 'nett_payment_gst')) ? $case_data['nett_payment_gst'] : 0,
                  'nett_payment_after_gst' => (float) (Common::validData($case_data, 'nett_payment_after_gst')) ? $case_data['nett_payment_after_gst'] : 0,
                  // 'payment' => (float) (Common::validData($case_data, 'payment')) ? $case_data['payment'] : 0,
                  // 'payment_gst' =>  (float) (Common::validData($case_data, 'payment_gst')) ? $case_data['payment_gst'] : 0,
                  'payment_mode_slug' => (Common::validData($case_data, 'payment_mode_slug')) ? $case_data['payment_mode_slug'] : null,
                  'payment_mode' => ($payment_mode) ? $payment_mode->title : null,
                  'submission_mode' => (Common::validData($case_data, 'submission_mode')) ? $case_data['submission_mode'] : 0
                ]);

                if ($riders_data->count() > 0) {
                  $riders_ape_total = 0;
                  foreach ($riders_data as $case_rider) {
                    if ($rider_record = Rider::firstUuid($case_rider->uuid)){
                      $case->riders()->attach($rider_record, [
                        'rider_name' => $rider_record->name,
                        'gst_rate' => (float) $case_rider->gst_rate ?? 0,
                        'sum_assured' => (float) $case_rider->sum_assured ?? 0,
                        'policy_term' => (float) $case_rider->policy_term ?? 0,
                        'payment_term' => (float) $case_rider->payment_term ?? 0,
                        'gross_payment_before_gst' => (float) $case_rider->gross_payment_before_gst ?? 0,
                        'gross_payment_gst' => (float) $case_rider->gross_payment_gst ?? 0,
                        'gross_payment_after_gst' => (float) $case_rider->gross_payment_after_gst ?? 0,
                        'payment_discount' => (float) $case_rider->payment_discount ?? 0,
                        'nett_payment_before_gst' => (float) $case_rider->nett_payment_before_gst ?? 0,
                        'nett_payment_gst' => (float) $case_rider->nett_payment_gst ?? 0,
                        'nett_payment_after_gst' => (float) $case_rider->nett_payment_after_gst ?? 0,
                      ]);

                      $riders_ape_total += $case_rider->ape;
                    }
                  }
                  if ($riders_ape_total) {
                    $case_ape = $case->ape + $riders_ape_total;
                    $case->update(['ape' => $case_ape]);
                  }
                }

                if (request()->has('files_app')) {
                    foreach(request()->file('files_app') as $file) {
                        $case->addMedia($file)->toMediaCollection('application-form');
                    }
                }

                if (request()->has('files_bi')) {
                    foreach(request()->file('files_bi') as $file) {
                        $case->addMedia($file)->toMediaCollection('benefit-illustration');
                    }
                }

                if (request()->has('files_giro')) {
                    foreach(request()->file('files_giro') as $file) {
                        $case->addMedia($file)->toMediaCollection('giro-form');
                    }
                }

                if (request()->has('files_medi')) {
                    foreach(request()->file('files_medi') as $file) {
                        $case->addMedia($file)->toMediaCollection('medical-report');
                    }
                }

                if (request()->has('files_supp')) {
                    foreach(request()->file('files_supp') as $file) {
                        $case->addMedia($file)->toMediaCollection('supplementary');
                    }
                }

                if (request()->has('files_doc')) {
                    foreach(request()->file('files_doc') as $file) {
                        $case->addMedia($file)->toMediaCollection('documents');
                    }
                }

                return response()->json([
                    'error' => false,
                    'data' => SubmissionHelper::index($submission)
                ]);


            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Store into database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCases($submission_uuid, $case_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_create')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($submission_uuid)) {
              if ($case = SubmissionCase::firstUuid($case_uuid)) {

                  request()->validate([
                      'submission' => 'required',
                      'case' => 'required',
                      'riders' => 'nullable',
                  ]);

                  $submission_data = collect(json_decode(request()->input('submission')));
                  $case_data = collect(json_decode(request()->input('case')));
                  $riders_data = collect(json_decode(request()->input('riders')));

                  $client = $submission->policy_holder;
                  $life_assured = null;
                  $product_option = null;
                  $product = null;
                  $product_cat = null;
                  $provider = null;

                  $submission_cat = SelectSubmissionCategory::firstSlug($submission_data['submission_cat_slug']);

                  if ($submission_data['submission_cat_slug'] == 'insurance') {
                      // Lets settle Life Assured first to get life_assured_uuid
                      if ($case_data['life_assured_is_client'] === false && $case_data['life_assured_type'] == "individual" && $case_data['life_assured_uuid']){
                        // Life assured exists
                        $life_assured = LifeAssured::firstUuid($case_data['life_assured_uuid']);
                      } else if ($case_data['life_assured_is_client'] === false && $case_data['life_assured_type'] == "individual" && $case_data['life_assured']) {
                        // Life assured does not exists, lets create one record now
                        $display_name = Common::trimStringUpper($case_data['life_assured']);
                        $life_assured = $client->findOrNewLifeAssured($display_name, ($case_data['nric_no'] ?? $case_data['fin_no'] ?? $case_data['passport_no'] ?? null));
                        if ($life_assured->is($client)) $case_data['life_assured_is_client'] = true;
                      }

                      // Update Life Assured Record
                      if ($case_data['relationship_type_slug']) $life_assured->update(['relationship_type_slug' => $case_data['relationship_type_slug']]);

                      if ($life_assured) {
                        // Get Individual Reference
                        $life_assured_individual = $life_assured->individual;
                        // Update Client Individual Record
                        ClientHelper::update($case_data->only(ClientHelper::fields('individual')), $life_assured_individual, 'individual');
                        // Update Client Individual :: Contact
                        ClientHelper::update($case_data->only(ClientHelper::fields('individual_contact')), $life_assured_individual, 'individual_contact');
                        // Update Client Individual :: Address
                        ClientHelper::update($case_data->only(ClientHelper::fields('individual_address')), $life_assured_individual, 'individual_address');

                        $life_assured_individual_contact = $life_assured_individual->contacts()->where('contact_type_slug', 'default')->first();
                        $life_assured_individual_address = $life_assured_individual->addresses()->where('address_type_slug', 'residential')->first();
                      }

                      // Retrieve product info
                      $product_option = ProductOption::firstUuid($case_data['product_option_uuid']);
                      $product = $product_option->product;
                      $product_cat = $product_option->category;
                      $provider = $product->provider;
                  } else if ($submission_data['submission_cat_slug'] == 'cis') {
                      // Retrieve product info
                      $product_option = ProductOption::firstUuid($case_data['product_option_uuid']);
                      $product = $product_option->product;
                      $product_cat = $product_option->category;
                      $provider = $product->provider;
                  } else if ($submission_data['submission_cat_slug'] == 'gi') {
                      // Retrieve product info
                      $provider = SelectProvider::where('slug', $case_data['provider_slug'])->first();
                      $product_cat = SelectProductCategory::where('slug', $case_data['product_cat_slug'])->first();
                  } else if ($submission_data['submission_cat_slug'] == 'wills') {
                      // Retrieve product info
                      $provider = SelectProvider::where('slug', $case_data['provider_slug'])->first();
                  }

                  $payment_mode = ($case_data['payment_mode_slug']) ? SelectPaymentMode::firstSlug($case_data['payment_mode_slug']) : null;

                  // Create Case Data
                  $case->update([
                    'life_assured_uuid' => (Common::validData($life_assured) && !$case_data['life_assured_is_client']) ? $life_assured->uuid : null,
                    'provider_slug' => (Common::validData($case_data, 'provider_slug')) ? $case_data['provider_slug'] : null,
                    'submission_cat_slug' => $submission_data['submission_cat_slug'] ?? null,
                    'product_cat_slug' => ($product_cat) ? $product_cat->slug : null,
                    'product_uuid' => (Common::validData($product)) ? $product->uuid : null,
                    'product_option_uuid' => (Common::validData($product_option)) ? $product_option->uuid : null,
                    // Snapshot Values
                    'currency' => (Common::validData($case_data, 'currency')) ? $case_data['currency'] : 'sgd',
                    'ape' => (float) (Common::validData($case_data, 'ape')) ? $case_data['ape'] : 0,
                    'provider_name' => ($provider) ? $provider->full_name : null,
                    'life_assured_name' => ($life_assured && $life_assured_individual && !$case_data['life_assured_is_client']) ? $life_assured_individual->full_name : null,
                    'life_assured_is_client' => (Common::validData($case_data, 'life_assured_is_client')) ? $case_data['life_assured_is_client'] : false,
                    // Life Assured Individual Record
                    'life_assured_personal' => ($life_assured && Common::validData($life_assured_individual) && !$case_data['life_assured_is_client']) ? [
                      'salutation_slug' => $life_assured_individual->salutation_slug ?? null,
                      'full_name' => $life_assured_individual->full_name ?? null,
                      'alias' => $life_assured_individual->alias ?? null,
                      'chinese_name' => $life_assured_individual->chinese_name ?? null,
                      'nric_no' => $life_assured_individual->nric_no ?? null,
                      'fin_no' => $life_assured_individual->fin_no ?? null,
                      'passport_no' => $life_assured_individual->passport_no ?? null,
                      'gender_slug' => $life_assured_individual->gender_slug ?? null,
                      'date_birth' => $life_assured_individual->date_birth ?? null,
                      'race_slug' => $life_assured_individual->race_slug ?? null,
                      'country_birth_slug' => $life_assured_individual->country_birth_slug ?? null,
                      'nationality_slug' => $life_assured_individual->nationality_slug ?? null,
                      'residency_status_slug' => $life_assured_individual->residency_status_slug ?? null,
                      'marital_status_slug' => $life_assured_individual->marital_status_slug ?? null,
                      'employment_status_slug' => $life_assured_individual->employment_status_slug ?? null,
                      'income_range' => $life_assured_individual->income_range ?? null,
                      'job_title' => $life_assured_individual->job_title ?? null,
                      'company_name' => $life_assured_individual->company_name ?? null,
                      'business_nature' => $life_assured_individual->business_nature ?? null,
                      'education_level_slug' => $life_assured_individual->education_level_slug ?? null,
                      'education_institution' => $life_assured_individual->education_institution ?? null,
                      'field_of_study' => $life_assured_individual->field_of_study ?? null,
                      'smoker' => $life_assured_individual->smoker ?? null,
                      'selected' => $life_assured_individual->selected ?? null,
                      'pdpa' => $life_assured_individual->pdpa ?? null,
                      'contact_information' => [
                        'home_no' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->home_no : null,
                        'mobile_no' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->mobile_no : null,
                        'fax_no' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->fax_no : null,
                        'email' => ($life_assured && $life_assured_individual_contact) ? $life_assured_individual_contact->email : null,
                      ],
                      'address_information' => [
                        'block' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->block : null,
                        'street' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->street : null,
                        'unit' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->unit : null,
                        'building' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->building : null,
                        'city' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->city : null,
                        'postal' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->postal : null,
                        'country_slug' => ($life_assured && $life_assured_individual_address) ? $life_assured_individual_address->country_slug : null,
                      ]
                    ] : null,
                    'submission_category' => $submission_cat->title ?? null,
                    'product_category' => ($product_cat) ? $product_cat->title : null,
                    'product_name' => ($product) ? $product->name : null,
                    'option_name' => ($product_option) ? $product_option->name : null,
                    'investment_account_type' => (Common::validData($case_data, 'investment_account_type')) ? $case_data['investment_account_type'] : null,
                    'investment_transaction_type' => (Common::validData($case_data, 'investment_transaction_type')) ? $case_data['investment_transaction_type'] : null,
                    'loan_property_address' => (Common::validData($case_data, 'loan_property_address')) ? $case_data['loan_property_address'] : null,
                    'loan_amount' => (float) (Common::validData($case_data, 'loan_amount')) ? $case_data['loan_amount'] : 0,
                    'loan_platform' => (float) (Common::validData($case_data, 'loan_platform')) ? $case_data['loan_platform'] : null,
                    'loan_bank_slug' => (float) (Common::validData($case_data, 'loan_bank_slug')) ? $case_data['loan_bank_slug'] : null,
                    'loan_interest_rate' => (float) (Common::validData($case_data, 'loan_interest_rate')) ? $case_data['loan_interest_rate'] : 0,
                    'policy_term' => (float) (Common::validData($case_data, 'policy_term')) ? $case_data['policy_term'] : 0,
                    'sum_assured' => (float) (Common::validData($case_data, 'sum_assured')) ? $case_data['sum_assured'] : 0,
                    'payment_term' => (float) (Common::validData($case_data, 'payment_term')) ? $case_data['payment_term'] : 0,
                    'payment_frequency' => (Common::validData($case_data, 'payment_frequency')) ? $case_data['payment_frequency'] : 0,
                    'payment_type' => (Common::validData($case_data, 'payment_type')) ? $case_data['payment_type'] : null,
                    'gst_rate' => (float) (Common::validData($case_data, 'gst_rate')) ? $case_data['gst_rate'] : 0,
                    'gross_payment_before_gst' => (float) (Common::validData($case_data, 'gross_payment_before_gst')) ? $case_data['gross_payment_before_gst'] : 0,
                    'gross_payment_gst' => (float) (Common::validData($case_data, 'gross_payment_gst')) ? $case_data['gross_payment_gst'] : 0,
                    'gross_payment_after_gst' => (float) (Common::validData($case_data, 'gross_payment_after_gst')) ? $case_data['gross_payment_after_gst'] : 0,
                    'payment_discount' => (float) (Common::validData($case_data, 'payment_discount')) ? $case_data['payment_discount'] : 0,
                    'nett_payment_before_gst' => (float) (Common::validData($case_data, 'nett_payment_before_gst')) ? $case_data['nett_payment_before_gst'] : 0,
                    'nett_payment_gst' => (float) (Common::validData($case_data, 'nett_payment_gst')) ? $case_data['nett_payment_gst'] : 0,
                    'nett_payment_after_gst' => (float) (Common::validData($case_data, 'nett_payment_after_gst')) ? $case_data['nett_payment_after_gst'] : 0,
                    'payment_mode_slug' => (Common::validData($case_data, 'payment_mode_slug')) ? $case_data['payment_mode_slug'] : null,
                    'payment_mode' => ($payment_mode) ? $payment_mode->title : null,
                    'submission_mode' => (Common::validData($case_data, 'submission_mode')) ? $case_data['submission_mode'] : 0
                  ]);

                  $case->riders()->detach();

                  if ($riders_data->count() > 0) {
                    $riders_ape_total = 0;
                    foreach ($riders_data as $case_rider) {
                      if ($rider_record = Rider::firstUuid($case_rider->uuid)){
                        $case->riders()->attach($rider_record, [
                          'rider_name' => $rider_record->name,
                          'gst_rate' => (float) $case_rider->gst_rate ?? 0,
                          'sum_assured' => (float) $case_rider->sum_assured ?? 0,
                          'policy_term' => (float) $case_rider->policy_term ?? 0,
                          'payment_term' => (float) $case_rider->payment_term ?? 0,
                          'gross_payment_before_gst' => (float) $case_rider->gross_payment_before_gst ?? 0,
                          'gross_payment_gst' => (float) $case_rider->gross_payment_gst ?? 0,
                          'gross_payment_after_gst' => (float) $case_rider->gross_payment_after_gst ?? 0,
                          'payment_discount' => (float) $case_rider->payment_discount ?? 0,
                          'nett_payment_before_gst' => (float) $case_rider->nett_payment_before_gst ?? 0,
                          'nett_payment_gst' => (float) $case_rider->nett_payment_gst ?? 0,
                          'nett_payment_after_gst' => (float) $case_rider->nett_payment_after_gst ?? 0,
                        ]);

                        $riders_ape_total += $case_rider->ape;
                      }
                    }
                    if ($riders_ape_total) {
                      $case_ape = $case->ape + $riders_ape_total;
                      $case->update(['ape' => $case_ape]);
                    }
                  }

                  return response()->json([
                      'error' => false,
                      'data' => SubmissionHelper::index($submission)
                  ]);


              } else {
                return Common::reject(404, 'case_not_found');
              }
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCases($uuid, $case_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              $case = $submission->cases()->where('uuid', $case_uuid)->first();
              $submission->log($user, 'submission_case_deleted', 'Submission Case record deleted.', $case, null, 'submissions_cases', $case->uuid);
              $case->delete();
              return response()->json([
                  'error' => false,
                  'data' => SubmissionCaseHelper::index($submission)
              ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }





    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCases($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionCaseHelper::index($submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityLogs($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ActivityLogHelper::index('submissions', $submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMedia($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => MediaHelper::index('submissions', $submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubmissionMedia($uuid, $media_id)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              $media = $submission->documents->where('id', $media_id)->first();
              // return response()->download($media->id .'/'.$media->file_name);
              // return response()->download('app/submissions/'.$media->id .'/'.$media->file_name);
              return Storage::disk('submissions')->download($media->id .'/'.$media->file_name);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }
    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCaseMedia($uuid, $case_uuid, $media_id)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              if ($case = $submission->cases()->where('uuid', $case_uuid)->first()) {
                $media = $case->documents->where('id', $media_id)->first();
                // return response()->download($media->id .'/'.$media->file_name);
                // return response()->download('app/submissions/'.$media->id .'/'.$media->file_name);
                return Storage::disk('submissions')->download($media->id .'/'.$media->file_name);
              } else {
                return Common::reject(404, 'case_not_found');
              }
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadFromOldPortal($case_uuid, $file_type)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = SubmissionCase::firstUuid($case_uuid)) {
                return SubmissionCaseHelper::downloadFromOldPortal($case_uuid, $file_type);
            } else {
              return Common::reject(404, 'case_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }

    /** ===================================================================================================
     * Store into database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadMedia($uuid, $collection_name)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_create')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
                request()->validate(['uploads' => 'required']);
                $count = 0;
                foreach(request()->file('uploads') as $file) {
                  $count++;
                  $submission->addMedia($file)->toMediaCollection($collection_name);
                }
                $submission->log($user, 'submission_uploaded_files', 'Uploaded '.$count.' files to '.$collection_name.'.', null, null, 'submissions', $submission->uuid);
                return response()->json([
                    'error' => false,
                    'data' => MediaHelper::index('submissions', $submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMedia($uuid, $media_id)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              $media = $submission->documents->where('id', $media_id)->first();
              $submission->log($user, 'submission_media_deleted', 'Submission media deleted.', $media, null, 'submissions', $submission->uuid);
              $media->delete();
              return response()->json([
                  'error' => false,
                  'data' => MediaHelper::index('submissions', $submission)
              ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }




    public function uploadMediaToCase($uuid, $case_uuid, $collection_name)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_create')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              if ($case = $submission->cases()->where('uuid', $case_uuid)->first()) {
                  request()->validate(['uploads' => 'required']);
                  $count = 0;
                  foreach(request()->file('uploads') as $file) {
                    $count++;
                    $case->addMedia($file)->toMediaCollection($collection_name);
                  }
                  $submission->log($user, 'submission_case_media_uploaded', 'Uploaded '.$count.' files to '.$collection_name.'.', null, null, 'submissions_cases', $case->uuid);
                  return response()->json([
                      'error' => false,
                      'data' => SubmissionCaseHelper::index($submission)
                  ]);
              } else {
                return Common::reject(404, 'case_not_found');
              }
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMediaFromCase($uuid, $case_uuid, $media_id)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              if ($case = $submission->cases()->where('uuid', $case_uuid)->first()) {
                  $media = $case->documents->where('id', $media_id)->first();
                  $submission->log($user, 'submission_case_media_deleted', 'Submission Case media deleted.', $media, null, 'submissions_cases', $case->uuid);
                  $media->delete();
                  return response()->json([
                      'error' => false,
                      'data' => SubmissionCaseHelper::index($submission)
                  ]);
              } else {
                return Common::reject(404, 'case_not_found');
              }
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }









    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function beginSubmission($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              $submission->log($user, 'submission_resumed', 'Submission draft redrafted from admin rejection.', null, null, 'submissions', $submission->uuid);
              $submission->update(['status_slug' => 'draft']);
              return response()->json([
                  'error' => false,
                  'data' => SubmissionHelper::index($submission)
              ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }







    /**
     * @return \Illuminate\Http\Response
     */
    public function submit($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              $submission_status = $submission->status;
              $next_step = $submission_status->step + 1;
              $next_status = SelectSubmissionStatus::firstStep($next_step);
              $submission->update(['status_slug' => $next_status->slug]);
              $submission->log($user, 'submission_draft_submitted', 'Submission draft submitted to admin.', null, $submission, 'submissions', $submission->uuid);
              return response()->json([
                  'error' => false,
                  'data' => SubmissionHelper::index($submission)
              ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }



    /**
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_submissions_delete')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($submission = Submission::firstUuid($uuid)) {
              $submission->log($user, 'submission_draft_deleted', 'Submission draft deleted.', $submission, null, 'submissions', $submission->uuid);
              $submission->delete();
              return response()->json(['error' => false]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
}
