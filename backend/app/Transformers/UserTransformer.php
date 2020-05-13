<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\LegacyFA\Personnels\Personnel;
use App\Models\Users\{User, UserPermission};
use App\Helpers\Common;

class UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        $array = [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => strtolower($user->email),
            'gender' => $user->gender->title,
            'designation' => $user->designation->title,
            'last_seen' => ($user->last_seen) ? $user->last_seen->toDateTimeString() : null,
            'dp_1x' => Common::dataOrNull($user->dp_thumb),
            'dp_2x' => Common::dataOrNull($user->dp_original),
            'activated' => (boolean) $user->activated
            // 'has_personnels' => ($is_admin) ? Personnel::active()->mapWithKeys(function($item) {
            //                                                 return [$item['lfa_sl_no'] => $item['uuid']];
            //                                             }) : (($user->personnels->count()) ? $user->personnels->mapWithKeys(function($item) {
            //                                                 return [$item['lfa_sl_no'] => $item['uuid']];
            //                                             }) : null),
        ];

        $array['setup'] = (boolean) $user->setup;
        $array['private'] = (boolean) $user->private;
        $array['is_admin'] = (boolean) $user->is_admin;
        $array['is_associate'] = (boolean) $user->is_associate;
        $array['is_staff'] = (boolean) $user->is_staff;
        $array['is_assistant'] = (boolean) $user->is_assistant;
        $array['is_candidate'] = (boolean) $user->is_candidate;
        $array['is_client'] = (boolean) $user->is_client;
        $array['is_guest'] = (boolean) $user->is_guest;
        $array['role'] = $user->roles->first()->name ?? null;
        $array['permissions'] = ($user->hasRole('super-admin')) ? UserPermission::all()->pluck('name') : $user->getAllPermissions()->pluck('name');

        if ($personnel = $user->default_personnel) {
            $array['associate'] = fractal($user->default_personnel, new PersonnelTransformer())->toArray()['data'];
            // $array['associate']['lfa_code'] = $personnel->lfa_code;
            // $array['associate']['designation'] = ($personnel->latest_designation) ? $personnel->latest_designation->title : null;
            $array['associate']['is_user'] = $array['is_rep'];
            // $array['associate']['is_manager'] = $personnel->is_manager;
        }

        return $array;
    }
}
