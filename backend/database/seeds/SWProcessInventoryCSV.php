<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Rap2hpoutre\FastExcel\{FastExcel, SheetCollection};

class SWProcessInventoryCSV extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $locations = collect(json_decode(Storage::get('seeders/locations.json'), true));
        $inventory = collect(json_decode(Storage::get('seeders/inventory.json'), true));
        $jobs = collect(json_decode(Storage::get('seeders/jobs.json'), true));
        $results_active = collect([]);
        $results_eol = collect([]);

        foreach ($inventory as $key => $value) {
            // if ($value['location_ref']) $this->command->info($locations->get($value['location_ref'])['name']);
            if ($value['imei'] || $value['serial']) {
                $location = 'Spireworks HQ';
                $status = 'IN';
                $address = null;
                if ($value['location_type'] !== 'hq' && $value['location_ref']) {
                    $location_ref = $locations->get($value['location_ref']);
                    $location = $location_ref['name'];
                    if ($mall = $location_ref['mall']) $location = $location . ' (' . $mall . ')';
                    $address = $location_ref['address'] ?? null;
                    $status = "OUT";
                }

                if (isset($value['eol']) && $value['eol'] === true) {
                    $status = 'EOL';
                    $results_eol->push([
                        'Brand Name' => $value['brand'],
                        'Model Name' => $value['model'],
                        'Colour' => $value['colour'],
                        'IMEI Number' => $value['imei'],
                        'IMEI Length' => strlen($value['imei']),
                        'Serial Number' => $value['serial'],
                        'Serial Length' => strlen($value['serial']),
                        'Status' => $status,
                        'Location' => $location,
                        'Address' => $address,
                        'Job Reference' => ($value['job_ref']) ? str_pad($jobs->get($value['job_ref'])['reference'], 5, '0', STR_PAD_LEFT) : null,
                        'Job Date' => ($value['job_ref']) ? Carbon::parse($jobs->get($value['job_ref'])['start'])->format('Y-m-d') : null,
                        'Remarks' => $value['remarks']
                    ]);
                } else {
                    $results_active->push([
                        'Brand Name' => $value['brand'],
                        'Model Name' => $value['model'],
                        'Colour' => $value['colour'],
                        'IMEI Number' => $value['imei'],
                        'IMEI Length' => strlen($value['imei']),
                        'Serial Number' => $value['serial'],
                        'Serial Length' => strlen($value['serial']),
                        'Status' => $status,
                        'Location' => $location,
                        'Address' => $address,
                        'Job Reference' => ($value['job_ref']) ? str_pad($jobs->get($value['job_ref'])['reference'], 5, '0', STR_PAD_LEFT) : null,
                        'Job Date' => ($value['job_ref']) ? Carbon::parse($jobs->get($value['job_ref'])['start'])->format('Y-m-d') : null,
                        'Remarks' => $value['remarks']
                    ]);
                }
            }
        }

        // $this->command->info($results);
        $sheets = new SheetCollection([
            'Active' => $results_active,
            'EOL' => $results_eol
        ]);

        (new FastExcel($sheets))->export(storage_path('app/sw-inventory-jobs.xlsx'));

        // foreach(Client::all() as $client) {
        //     $client->log(null, 'created', 'Client record created.');

        //     foreach ($client->submissions as $submission) {
        //         $log = $client->log($submission->associate->user, 'submission_created', 'New Submission record created.', null, $submission, 'submissions', $submission->uuid, $submission->date_submission);
        //     }
        // }
    }
}
