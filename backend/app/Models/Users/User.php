<?php

namespace App\Models\Users;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

// 3rd Party Libraries
use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use Spatie\Permission\Traits\HasRoles;

// Local References
use App\Models\Selections\LegacyFA\{SelectDesignation, SelectOnboardingStatus};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\Individuals\Individual;
use App\Models\Users\{UserResetPassword, UserEmailToken, UserDevice};
use App\Traits\{ScopeFirstUuid, HasIndividual, HasLogs};

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use Notifiable,
        HasApiTokens,
        SoftDeletes,
        HasRoles,
        HasMediaTrait,
        HasLogs,
        ScopeFirstUuid,
        HasIndividual;

    protected $guard_name = 'api';

    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_users';

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'remember_token'
    ];

    /** ===================================================================================================
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'date_lfa_application' => 'datetime:Y-m-d',
        'date_ceo_interview' => 'datetime:Y-m-d',
        'date_contract_start' => 'datetime:Y-m-d',
        'date_onboarded' => 'datetime:Y-m-d',
        'date_offboarded' => 'datetime:Y-m-d',
        'date_resigned' => 'datetime:Y-m-d',
        'date_last_day' => 'datetime:Y-m-d',
        'deleted_at' => 'datetime',
    ];
    public function setDateLfaApplicationAttribute($date) { $this->attributes['date_lfa_application'] = Carbon::parse($date); }
    public function setDateCeoInterviewAttribute($date) { $this->attributes['date_ceo_interview'] = Carbon::parse($date); }
    public function setDateContractStartAttribute($date) { $this->attributes['date_contract_start'] = Carbon::parse($date); }
    public function setDateOnboardedAttribute($date) { $this->attributes['date_onboarded'] = Carbon::parse($date); }
    public function setDateOffboardedAttribute($date) { $this->attributes['date_offboarded'] = Carbon::parse($date); }
    public function setDateResignedAttribute($date) { $this->attributes['date_resigned'] = Carbon::parse($date); }
    public function setDateLastDayAttribute($date) { $this->attributes['date_last_day'] = Carbon::parse($date); }

    /** ===================================================================================================
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'email';
    }

    /** ===================================================================================================
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

    /** ===================================================================================================
     *  Setup model event hooks
     *
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function password_resets() { return $this->hasMany(UserResetPassword::class, 'user_uuid', 'uuid'); }
    public function verify_email() { return $this->hasOne(UserEmailToken::class, 'user_uuid', 'uuid'); }
    public function designation() { return $this->belongsTo(SelectDesignation::class,'designation_slug','slug'); }
    public function onboarding_status() { return $this->belongsTo(SelectOnboardingStatus::class,'onboarding_status_slug', 'slug'); }
    public function sales_associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function spouse() { return $this->belongsTo(Individual::class, 'spouse_uuid', 'uuid'); }
    public function onboarded_by() { return $this->belongsTo(User::class, 'onboarded_by', 'uuid'); }
    public function offboarded_by() { return $this->belongsTo(User::class, 'offboarded_by', 'uuid'); }
    public function devices() { return $this->hasMany(UserDevice::class, 'user_uuid', 'uuid'); }
    public function associate_access() { return $this->belongsToMany(Associate::class, 'associates_access', 'user_uuid', 'associate_uuid', 'uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getNameAttribute() { return $this->individual->full_name; }
    public function getGenderAttribute() { return $this->individual->gender; }
    public function getIsAdminAttribute() { return $this->hasRole('super-admin'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function action() { return $this->update(['last_seen' => Carbon::now()]); }
    public function update_designation()
    {
        // Check if associate is salesforce
        if ($this->sales_associate && $this->sales_associate->latest_designation) {
            $this->designation_slug = $this->sales_associate->latest_designation->slug ?? null;
            $this->save();
        }
        return $this->fresh();
    }


    /** ===================================================================================================
     * Media Collections
     *
     */
    public function getDpThumbAttribute() { return $this->getFirstMediaUrl('display_photo', 'thumbnail'); }
    public function getDpOriginalAttribute() { return $this->getFirstMediaUrl('display_photo'); }
    public function addDp($pathToFile)
    {
        return $this->addMedia($pathToFile)
                    ->setName($this->name)
                    ->setFileName($this->uuid . '.' . pathinfo($pathToFile, PATHINFO_EXTENSION))
                    ->toMediaCollection('display_photo');
    }
    public function addDpUrl($pathToFile)
    {
        return $this->addMediaFromUrl($pathToFile)
                    ->setName($this->name)
                    ->setFileName($this->uuid . '.' . pathinfo($pathToFile, PATHINFO_EXTENSION))
                    ->toMediaCollection('display_photo');
    }
    public function registerMediaCollections()
    {
        $this->addMediaCollection('display_photo')
             ->useDisk('display_photo')
             ->singleFile();
    }
    public function registerMediaConversions(\Spatie\MediaLibrary\Models\Media $media = null)
    {
        $this->addMediaConversion('thumbnail')->width(100)->height(100);
    }
}
