<?php

namespace App\Models\LegacyFA\Associates;
use Carbon\Carbon;
use App\Models\LegacyFA\Associates\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use Illuminate\Support\Str;
use App\Models\Selections\LegacyFA\{SelectRNFStatus};
use App\Models\Users\User;
use App\Models\LegacyFA\Clients\{ClientPolicy, ClientPolicyTransaction};
use App\Models\LegacyFA\Associates\{ProviderCode};
use App\Models\LegacyFA\Payroll\PayrollComputation;
use App\Models\LegacyFA\Submissions\{Submission, SubmissionCase, IntroducerCase};
use App\Traits\{ScopeFirstUuid,
                ScopeFirstSn,
                ScopeFirstAaCode,
                ScopeFirstLfaCode,
                HasUsers,
                HasTeams,
                HasClients,
                HasMovements,
                HasBandings,
                // HasSubmissions,
                HasPayrollInstructions,
                HasLogs};

// use App\Models\LegacyFA\Payroll\Traits\{HasPayrollRates, HasPayrollInstructions, HasPayrollStatements, HasEliteSchemePayments};

class Associate extends BaseModel implements HasMedia
{
    use SoftDeletes,
        HasMovements,
        HasBandings,
        HasTeams,
        HasUsers,
        HasClients,
        // HasSubmissions,
        HasLogs,
        // HasPayrollRates,
        HasPayrollInstructions,
        // HasPayrollStatements,
        // HasEliteSchemePayments,
        HasMediaTrait,
        ScopeFirstUuid,
        ScopeFirstSn,
        ScopeFirstAaCode,
        ScopeFirstLfaCode;


    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'associates';


    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid'
    ];


    /** ===================================================================================================
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];


    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_rnf_submission' => 'datetime:Y-m-d',
        'date_rnf_approval' => 'datetime:Y-m-d',
        'date_rnf_withdrawal' => 'datetime:Y-m-d',
        'date_rnf_cessation' => 'datetime:Y-m-d',
        'deleted_at' => 'datetime:Y-m-d',
        'date_m9' => 'datetime:Y-m-d',
        'date_m9a' => 'datetime:Y-m-d',
        'date_m5' => 'datetime:Y-m-d',
        'date_hi' => 'datetime:Y-m-d',
        'date_m8' => 'datetime:Y-m-d',
        'date_m8a' => 'datetime:Y-m-d',
        'date_cert_ilp' => 'datetime:Y-m-d',
        'date_cert_li' => 'datetime:Y-m-d',
        'date_cert_fna' => 'datetime:Y-m-d',
        'date_cert_bcp' => 'datetime:Y-m-d',
        'date_cert_pgi' => 'datetime:Y-m-d',
        'date_cert_comgi' => 'datetime:Y-m-d',
        'date_cert_cgi' => 'datetime:Y-m-d',
    ];

    public function setDateRnfSubmissionAttribute($date) { $this->attributes['date_rnf_submission'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateRnfApprovalAttribute($date) { $this->attributes['date_rnf_approval'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateRnfWithdrawalAttribute($date) { $this->attributes['date_rnf_withdrawal'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateRnfCessationAttribute($date) { $this->attributes['date_rnf_cessation'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateM9Attribute($date) { $this->attributes['date_m9'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateM9aAttribute($date) { $this->attributes['date_m9a'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateM5Attribute($date) { $this->attributes['date_m5'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateHiAttribute($date) { $this->attributes['date_hi'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateM8Attribute($date) { $this->attributes['date_m8'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateM8aAttribute($date) { $this->attributes['date_m8a'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertIlpAttribute($date) { $this->attributes['date_cert_ilp'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertLiAttribute($date) { $this->attributes['date_cert_li'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertFnaAttribute($date) { $this->attributes['date_cert_fna'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertBcpAttribute($date) { $this->attributes['date_cert_bcp'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertPgiAttribute($date) { $this->attributes['date_cert_pgi'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertComgiAttribute($date) { $this->attributes['date_cert_comgi'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateCertCgiAttribute($date) { $this->attributes['date_cert_cgi'] = ($date) ? Carbon::parse($date) : null; }


    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }


    /** ===================================================================================================
     *  Setup model event hooks
     *
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = Str::orderedUuid()->toString();
        });
        self::created(function ($model) {
            $model->log(auth()->user(), 'associate_created', 'Associate record created.', null, $model, 'associates', $model->uuid);
        });
    }


    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function submissions() { return $this->hasMany(Submission::class, 'associate_uuid', 'uuid'); }
    public function submission_cases() { return $this->hasMany(SubmissionCase::class, 'associate_uuid', 'uuid'); }
    public function introducer_case() { return $this->hasMany(IntroducerCase::class, 'introducer_uuid', 'uuid'); }
    public function rnf_status() { return $this->belongsTo(SelectRNFStatus::class,'rnf_status_slug', 'slug'); }
    public function providers_codes() { return $this->hasMany(ProviderCode::class, 'associate_uuid', 'uuid'); }

    public function payroll_computations() { return $this->hasMany(PayrollComputation::class, 'payee_uuid', 'uuid'); }

    public function policies() { return $this->hasMany(ClientPolicy::class, 'associate_uuid', 'uuid'); }
    public function policies_premiums() { return $this->hasManyThrough(ClientPolicyTransaction::class, ClientPolicy::class, 'associate_uuid', 'policy_uuid', 'uuid', 'uuid'); }

    // Untested
    public function production_fyp() { return $this->policies_premiums()->where('commission_type', 'first-year')->sum('premium'); }
    public function production_fyc() { return $this->payroll_computations()->where('computations.commission_type', 'first-year')->where('commission_tier', 1)->sum('amount'); }

    public function users_access() { return $this->belongsToMany(User::class, 'associates_access', 'associate_uuid', 'user_uuid', 'uuid', 'uuid'); }
    public function user() { return $this->hasOne(User::class, 'associate_uuid', 'uuid'); }
    public function getNameAttribute() { return $this->user->name; }

    /** ===================================================================================================
     * Scope a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function scopeActive($query)
    {
        return $query->whereHas('movements', function ($q) {
            return $q->where('date_end', '>', Carbon::now());
        })->get();
    }

    public function scopeInactive($query)
    {
        $active = $query->whereHas('movements', function ($q) {
            return $q->where('date_end', 'M-', Carbon::now());
        })->get();
        $associates = Associate::all();
        return $associates->diff($active);
    }


    /** ===================================================================================================
     * Media Collections
     *
     */
}
