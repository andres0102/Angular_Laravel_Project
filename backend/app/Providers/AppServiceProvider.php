<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        \URL::forceScheme('https');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'individuals' => 'App\Models\Individuals\Individual',
            'contact' => 'App\Models\Individuals\Contact',
            'address' => 'App\Models\Individuals\Address',
            'bank' => 'App\Models\Individuals\Bank',
            'users' => 'App\Models\Users\User',

            'associates' => 'App\Models\LegacyFA\Associates\Associate',
            'banding_gi' => 'App\Models\LegacyFA\Associates\BandingGI',
            'banding_lfa' => 'App\Models\LegacyFA\Associates\BandingLFA',
            'movements' => 'App\Models\LegacyFA\Associates\Movement',
            'providers_codes' => 'App\Models\LegacyFA\Associates\ProviderCode',

            'clients' => 'App\Models\LegacyFA\Clients\Client',
            'client_aliases' => 'App\Models\LegacyFA\Clients\ClientAlias',
            'life_assured' => 'App\Models\LegacyFA\Clients\LifeAssured',
            'client_policies' => 'App\Models\LegacyFA\Clients\ClientPolicy',
            'client_policies_transactions' => 'App\Models\LegacyFA\Clients\ClientPolicyTransaction',
            'introducers' => 'App\Models\LegacyFA\Clients\Introducer',
            'introducers_gifts' => 'App\Models\LegacyFA\Clients\IntroducerGift',
            'nominees' => 'App\Models\LegacyFA\Clients\Nominee',

            'payroll_batches' => 'App\Models\LegacyFA\Payroll\PayrollBatch',
            'payroll_feeds' => 'App\Models\LegacyFA\Payroll\PayrollFeed',
            'payroll_records' => 'App\Models\LegacyFA\Payroll\PayrollRecord',
            'payroll_computations' => 'App\Models\LegacyFA\Payroll\PayrollComputation',
            'payroll_instructions' => 'App\Models\LegacyFA\Payroll\Instruction',
            'payroll_firm_codes' => 'App\Models\LegacyFA\Payroll\FirmCode',

            'products' => 'App\Models\LegacyFA\Products\Product',
            'products_options' => 'App\Models\LegacyFA\Products\ProductOption',
            'riders' => 'App\Models\LegacyFA\Products\Rider',

            'submissions' => 'App\Models\LegacyFA\Submissions\Submission',
            'submissions_cases' => 'App\Models\LegacyFA\Submissions\SubmissionCase',
            'submissions_cases_info' => 'App\Models\LegacyFA\Submissions\SubmissionCaseInfo',
            'introducer_cases' => 'App\Models\LegacyFA\Submissions\IntroducerCase',

            'teams' => 'App\Models\LegacyFA\Teams\Team',
            'teams_membership' => 'App\Models\LegacyFA\Teams\Membership',
            'teams_invitations' => 'App\Models\LegacyFA\Teams\Invitation',

            'notices' => 'App\Models\General\Notice',
            'logs' => 'App\Models\General\Log',
            'comments' => 'App\Models\General\Comment',
        ]);
    }
}
