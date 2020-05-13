<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LegacyFA\Products\{Product, ProductOption, Rider};
use App\Models\Selections\{SelectAddressType,
                           SelectContactType,
                           SelectBank,
                           SelectCountry,
                           SelectCurrency,
                           SelectEducationalLevel,
                           SelectEmploymentStatus,
                           SelectGender,
                           SelectLanguage,
                           SelectMaritalStatus,
                           SelectRace,
                           SelectRelationshipType,
                           SelectResidencyStatus,
                           SelectSalutation};
use App\Models\Selections\LegacyFA\{SelectAllowanceScheme,
                                    SelectClientSource,
                                    SelectClientType,
                                    SelectConversationType,
                                    SelectDesignation,
                                    SelectLeadStage,
                                    SelectNomineeBenefit,
                                    SelectNomineeStatus,
                                    SelectOnboardingStatus,
                                    SelectOutcome,
                                    SelectPaymentMode,
                                    SelectPayrollCategory,
                                    SelectPayrollFeedMapping,
                                    SelectPayrollFeedType,
                                    SelectProductCategory,
                                    SelectProductCoverage,
                                    SelectProvider,
                                    SelectRNFStatus,
                                    SelectSalesActivity,
                                    SelectSalesStage,
                                    SelectSubmissionCategory,
                                    SelectSubmissionStatus,
                                    SelectTeamRole,
                                    SelectTeamType};
use App\Models\LegacyFA\Teams\Team;
use App\Models\LegacyFA\Associates\Associate;

class PagesController extends Controller
{
    /**
     * Create a new PagesController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        $this->middleware('auth', ['except' => [
            'index'
        ]]);
    }

    /** ===================================================================================================
     * This API provides searching of address data for a given search value. It returns search results with both latitude, longitude and x, y coordinates of the searched location.
     * Each page of json response is restricted to a maximum of 10 results.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Validate the search term for the given request.
        request()->validate(['term' => 'required']);

        $mapper = new OneMapHelper();
        $response = $mapper->search([
          'searchVal' => request()->input('term'),
          'returnGeom' => 'Y',
          'getAddrDetails' => 'Y',
          'pageNum' => request()->input('pageNum') ?? 1
        ])->getContents();
        $json_results = json_decode($response);

        return response()->json([
          'error' => false,
          'data' => $json_results
        ]);
    }

    /**
     * Display the selection lists as requested.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ($lists_array = request()->input('lists')) {
            $results = [];
            foreach($lists_array as $item) {
                switch ($item) {
                    case 'address-type':
                        $results[$item] = SelectAddressType::activated()->get();
                        break;
                    case 'contact-type':
                        $results[$item] = SelectContactType::activated()->get();
                        break;
                    case 'gender':
                        $results[$item] = SelectGender::activated()->get();
                        break;
                    case 'bank':
                        $results[$item] = SelectBank::activated()->get();
                        break;
                    case 'country':
                        $results[$item] = SelectCountry::all();
                        break;
                    case 'currency':
                        $results[$item] = SelectCurrency::all();
                        break;
                    case 'educational-level':
                        $results[$item] = SelectEducationalLevel::activated()->get();
                        break;
                    case 'employment-status':
                        $results[$item] = SelectEmploymentStatus::activated()->get();
                        break;
                    case 'language':
                        $results[$item] = SelectLanguage::all();
                        break;
                    case 'marital-status':
                        $results[$item] = SelectMaritalStatus::activated()->get();
                        break;
                    case 'race':
                        $results[$item] = SelectRace::activated()->get();
                        break;
                    case 'relationship-type':
                        $results[$item] = SelectRelationshipType::activated()->get();
                        break;
                    case 'residency-status':
                        $results[$item] = SelectResidencyStatus::activated()->get();
                        break;
                    case 'salutation':
                        $results[$item] = SelectSalutation::activated()->get();
                        break;
                    case 'lfa-allowance-scheme':
                        $results[$item] = SelectAllowanceScheme::activated()->get();
                        break;
                    case 'lfa-client-source':
                        $results[$item] = SelectClientSource::activated()->get();
                        break;
                    case 'lfa-client-type':
                        $results[$item] = SelectClientType::activated()->get();
                        break;
                    case 'lfa-conversation-type':
                        $results[$item] = SelectConversationType::activated()->get();
                        break;
                    case 'lfa-designation':
                        $results[$item] = SelectDesignation::activated()->get();
                        break;
                    case 'lfa-associates-designations':
                        $results[$item] = SelectDesignation::activated()->where('salesforce', true)->get();
                        break;
                    case 'lfa-lead-stage':
                        $results[$item] = SelectLeadStage::activated()->get();
                        break;
                    case 'lfa-nominee-benefit':
                        $results[$item] = SelectNomineeBenefit::activated()->get();
                        break;
                    case 'lfa-nominee-status':
                        $results[$item] = SelectNomineeStatus::activated()->get();
                        break;
                    case 'lfa-onboarding-status':
                        $results[$item] = SelectOnboardingStatus::activated()->get();
                        break;
                    case 'lfa-outcome':
                        $results[$item] = SelectOutcome::activated()->get();
                        break;
                    case 'lfa-payment-mode':
                        $results[$item] = SelectPaymentMode::activated()->get();
                        break;
                    case 'lfa-payroll-category':
                        $results[$item] = SelectPayrollCategory::activated()->get();
                        break;
                    case 'lfa-payroll-feed-mapping':
                        $results[$item] = SelectPayrollFeedMapping::all();
                        break;
                    case 'lfa-payroll-feed-type':
                        $results[$item] = SelectPayrollFeedType::activated()->get();
                        break;
                    case 'lfa-product-category':
                        $results[$item] = SelectProductCategory::activated()->get();
                        break;
                    case 'lfa-product-coverage':
                        $results[$item] = SelectProductCoverage::activated()->get();
                        break;
                    case 'lfa-product-series-by-providers':
                        $active_options = ProductOption::activated()->where('date_end', env('CO_LAST_DAY'))->get()->unique('product_uuid')->groupBy('provider_slug');
                        $products_arr = [];
                        foreach($active_options as $provider_slug => $product_options) {
                            $products_arr[$provider_slug] = Product::whereIn('uuid', collect($product_options)->pluck('product_uuid'))->get();
                        }
                        $results[$item] = $products_arr;
                        break;
                    case 'lfa-product-options-by-providers':
                        $results[$item] = ProductOption::activated()->where('date_end', env('CO_LAST_DAY'))->get()->groupBy('provider_slug');
                        break;
                    case 'lfa-product-options-by-providers-all':
                        $results[$item] = ProductOption::all()->groupBy('provider_slug');
                        break;
                    case 'lfa-product-options-by-series':
                        $results[$item] = ProductOption::activated()->where('date_end', env('CO_LAST_DAY'))->get()->groupBy('product_uuid');
                        break;
                    case 'lfa-riders-by-options':
                        $results[$item] = ProductOption::activated()->where('date_end', env('CO_LAST_DAY'))->with('riders')->get()->mapWithKeys(function($item){return [$item->uuid => $item->riders->toArray()];});
                        break;
                    case 'lfa-riders-by-options-all':
                        $results[$item] = ProductOption::with('riders')->get()->mapWithKeys(function($item){return [$item->uuid => $item->riders->toArray()];});
                        break;
                    case 'lfa-provider':
                        // Provider Codes
                        $results[$item] = SelectProvider::activated()->get();
                        break;
                    case 'lfa-provider-by-submission-cat':
                        $results[$item] = [
                            'insurance' => SelectProvider::whereIn('slug', ProductOption::activated()->whereNotIn('product_cat_slug',['cis'])->where('date_end', env('CO_LAST_DAY'))->get()->unique('provider_slug')->pluck('provider_slug'))->get(),
                            'cis' => SelectProvider::whereIn('slug', ProductOption::activated()->whereIn('product_cat_slug',['cis'])->where('date_end', env('CO_LAST_DAY'))->get()->unique('provider_slug')->pluck('provider_slug'))->get(),
                            'gi' => SelectProvider::whereIn('slug', SelectSubmissionCategory::activated()->with('providers:slug')->whereHas('providers',function($q){$q->where('submission_cat_slug','gi');})->first()->providers->pluck('slug'))->get(),
                            'loans' => null,
                            'wills' => SelectProvider::whereIn('slug', SelectSubmissionCategory::activated()->with('providers:slug')->whereHas('providers',function($q){$q->where('submission_cat_slug','wills');})->first()->providers->pluck('slug'))->get(),
                        ];
                        break;
                    case 'lfa-rnf-status':
                        $results[$item] = SelectRNFStatus::activated()->get();
                        break;
                    case 'lfa-sales-activity':
                        $results[$item] = SelectSalesActivity::activated()->get();
                        break;
                    case 'lfa-sales-stage':
                        $results[$item] = SelectSalesStage::activated()->get();
                        break;
                    case 'lfa-submission-category':
                        $results[$item] = SelectSubmissionCategory::activated()->get();
                        break;
                    case 'lfa-submission-status':
                        $results[$item] = SelectSubmissionStatus::activated()->get();
                        break;
                    case 'lfa-team-role':
                        $results[$item] = SelectTeamRole::activated()->get();
                        break;
                    case 'lfa-team-type':
                        $results[$item] = SelectTeamType::activated()->get();
                        break;
                    case 'lfa-teams-associates-groups':
                        $results[$item] = Team::where('type_slug', 'group')->get();
                        break;
                    case 'lfa-teams-associates-units':
                        $results[$item] = Team::where('type_slug', 'unit')->get();
                        break;
                    case 'lfa-teams-associates-code':
                        $current_last_associate = (int) Associate::where('lfa_sl_no', '<', 5000)->orderBy('lfa_sl_no','desc')->first()->lfa_sl_no;
                        $results[$item] = $current_last_associate + 1;
                        break;
                    case 'lfa-associates':
                        $results[$item] = Associate::all()->transform(function($item) { return ['uuid'=>$item->uuid,'name'=>$item->name];});
                        break;
                }
            }
            return response()->json([
                'data' => $results,
                'error' => false
            ]);
        } else {
            return response()->json([
                'status' => 'no_list_found',
                'error' => true
            ]);
        }
    }
}
