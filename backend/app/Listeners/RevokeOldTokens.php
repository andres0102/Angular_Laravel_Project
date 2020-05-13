<?php

namespace App\Listeners;

use App\Models\Users\User;
use App\Events\UserAuthenticated;

use Laravel\Passport\Events\AccessTokenCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;

class RevokeOldTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AccessTokenCreated  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        $user = User::where('id', $event->userId)->first();
        $timestamp = $user->freshTimestamp();
        $user->log($user, 'login', 'User has logged in', null, null, 'users', $user->uuid, $timestamp);
        $user->update(['last_seen' => $timestamp]);

        $email = $user->email;
        event(new UserAuthenticated($email));

        DB::table('oauth_access_tokens')
            ->where('id', '<>', $event->tokenId)
            ->where('user_id', $event->userId)
            ->where('client_id', $event->clientId)
            ->update(['revoked' => true]);
    }
}
