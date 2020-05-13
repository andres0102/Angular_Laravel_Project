<?php

namespace App\Models\LegacyFA\Clients;
use App\Models\LegacyFA\Clients\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use App\Traits\{ScopeFirstUuid,
                HasIndividual,
                HasLifeAssured,
                HasPolicy,
                HasSubmission,
                HasLogs};
use App\Models\Selections\LegacyFA\{SelectClientType};
use App\Models\General\ActivityLog;
use App\Models\LegacyFA\Associates\{Associate};
use App\Models\LegacyFA\Clients\{ClientAlias, Introducer, Nominee, LifeAssured, ClientPolicy, SalesActivity};
use App\Models\LegacyFA\Submissions\{Submission, SubmissionCase, IntroducerCase};
use App\Models\LegacyFA\Payroll\{PayrollRecord, PayrollComputation};

class Client extends BaseModel implements HasMedia
{
    use SoftDeletes,
        HasMediaTrait,
        HasSubmission,
        HasPolicy,
        HasIndividual,
        HasLogs,
        HasLifeAssured,
        ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clients';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid'
    ];

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

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
            $model->log(auth()->user(), 'created', 'Client record created.', null, $model, 'clients', $model->uuid);
        });
    }


    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function aliases() { return $this->hasMany(ClientAlias::class, 'client_uuid', 'uuid'); }
    public function introducers() { return $this->hasMany(Introducer::class, 'client_uuid', 'uuid'); }
    public function nominees() { return $this->hasMany(Nominee::class, 'client_uuid', 'uuid'); }
    public function who_is_nominee() { return $this->hasOne(Nominee::class, 'converted_client_uuid', 'uuid'); }
    public function type() { return $this->belongsTo(SelectClientType::class, 'client_type_slug', 'slug'); }
    public function introducer_case() { return $this->hasMany(IntroducerCase::class, 'introducer_uuid', 'uuid'); }
    public function sales_activities() { return $this->hasMany(SalesActivity::class, 'client_uuid', 'uuid'); }
    public function payroll_records() { return $this->hasMany(PayrollRecord::class, 'client_uuid', 'uuid'); }


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

    /** ===================================================================================================
     *  Custom Functions
     *
     */
    public function mergeTo($client) {
        // --- Merge individual data
        $individual = $this->individual;
        $individual_data = collect($individual)->filter()->forget(['uuid', 'created_at', 'updated_at', 'deleted_at'])->toArray();
        // $client->individual->update($individual_data);
        foreach($individual_data as $i_key => $i_data) { if (!$client->individual[$i_key]) $client->individual->update([$i_key => $i_data]); }
        // --- Merge individual - contacts
        $default_contact = $individual->contacts->where('contact_type_slug','default')->first();
        $contact_data = collect($default_contact)->filter()->forget(['individual_uuid', 'contact_type_slug', 'created_at', 'updated_at'])->toArray();
        $client_contact = $client->individual->contacts->where('contact_type_slug','default')->first();
        // $client_contact->update($contact_data)
        if ($client_contact) {
            foreach($contact_data as $c_key => $c_data) { if (!$client_contact[$c_key]) $client_contact->update([$c_key => $c_data]); }
        } else $client->individual->contacts()->create(array_merge($contact_data, ['contact_type_slug' => 'default']));
        // --- Merge individual - address
        $default_address = $individual->addresses->where('address_type_slug','residential')->first();
        $address_data = collect($default_address)->filter()->forget(['individual_uuid', 'address_type_slug', 'created_at', 'updated_at'])->toArray();
        $client_address = $client->individual->addresses->where('address_type_slug','residential')->first();
        // $client_address->update($address_data)
        if ($client_address) {
            foreach($address_data as $a_key => $a_data) { if (!$client_address[$a_key]) $client_address->update([$a_key => $a_data]); }
        } else $client->individual->addresses()->create(array_merge($address_data, ['address_type_slug' => 'residential']));
        // --- Amend all aliases
        ClientAlias::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Remove duplicated aliases (if any)
        $aliases = ClientAlias::where('client_uuid', $client->uuid)->get();
        $unique_aliases = $aliases->unique('full_name')->pluck('full_name');
        foreach($unique_aliases as $u_alias) {
            if ($aliases->where('full_name', $u_alias)->count() > 1) {
                $alias_to_save = $aliases->where('full_name', $u_alias)->first();
                ClientAlias::where('client_uuid', $this->uuid)->where('full_name', $u_alias)->where('id', '<>', $alias_to_save->id)->delete();
            }
        }
        // --- Amend all life_assured
        LifeAssured::where('policy_holder_uuid', $this->uuid)->update(['policy_holder_uuid' => $client->uuid]);
        LifeAssured::where('individual_uuid', $individual->uuid)->update(['individual_uuid' => $client->individual->uuid]);
        // --- Amend all introducers
        Introducer::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all nominees
        Nominee::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all nominee - lead record
        Nominee::where('converted_client_uuid', $this->uuid)->update(['converted_client_uuid' => $client->uuid]);
        // --- Amend all payroll records
        PayrollRecord::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all payroll computations
        PayrollComputation::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all policies
        ClientPolicy::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all submissions
        Submission::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all submissions cases
        SubmissionCase::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all submissions introducer cases
        IntroducerCase::where('client_uuid', $this->uuid)->update(['client_uuid' => $client->uuid]);
        // --- Amend all logs
        ActivityLog::where('loggable_type', 'clients')->where('loggable_id', $this->id)->update(['loggable_id' => $client->id]);
        // --- Update Client Record
        foreach([
            'description',
            'interest',
            'important',
        ] as $client_key) {
            if (!$client[$client_key]) $client->update([$client_key => $this[$client_key]]);
        }
        // Remove Client Data
        $this->delete();
    }
}
