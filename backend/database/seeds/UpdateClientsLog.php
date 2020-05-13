<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\LegacyFA\Clients\Client;
use App\Models\General\ActivityLog;

class UpdateClientsLog extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        ActivityLog::truncate();
        // $date = Carbon::parse('2020-01');

        foreach(Client::all() as $client) {
            $client->log(null, 'created', 'Client record created.');

            foreach ($client->submissions as $submission) {
                $log = $client->log($submission->associate->user, 'submission_created', 'New Submission record created.', null, $submission, 'submissions', $submission->uuid, $submission->date_submission);
            }
        }
    }
}
