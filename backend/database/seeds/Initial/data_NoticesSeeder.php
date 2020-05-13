<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\General\Notice;

class data_NoticesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sample_notices = [
        [
          'title' => 'Fire Drill Exercise',
          'details' => '<p>Fire Drill exercise which will be conducted on:&nbsp;</p><p><strong>Wednesday 18 Dec 2019</strong></p><p>Fire Alarm will be ringing followed by evacuation announcement over the Public Address System and lifts will also be grounded for a short period of time during the Fire Drill exercise.&nbsp;</p><p>The Fire Drill will be postponed in case of inclement weather.&nbsp;</p><p>All occupants are encourage to actively participate in the exercise with the key objective of “keep everyone safe” in the event of an emergency.</p>',
          'full_day' => true,
          'location' => 'Legacy FA @ 1 Kay Siang Road',
          'start_date' => '2019-12-18',
          'end_date' => '2019-12-18',
          'created_at' => '2019-12-20',
          'important' => true
        ],
        [
          'title' => 'Christmas Celebration 2019',
          'details' => '<p>HO HO HO is coming to Legacy.&nbsp;</p><p>This year’s Christmas extravaganza will be an eye catching and wholesome experience. One of its kind.&nbsp;</p><p>&nbsp;</p><p><i>Details of our party as follows:&nbsp;</i></p><p>Date: <strong>Monday, 23 December 2019</strong>&nbsp;</p><p>Time: <strong>4pm till late of the night</strong>&nbsp;</p><p>Theme: <strong>Green and Red</strong>&nbsp;</p><p>&nbsp;</p><p>Beside many fun and games activities, scrumptious dinner will be served. Those with culinary skills are cordially encouraged to bring their finger licking good food as part of the “POT LUCK”.</p>',
          'location' => 'Legacy FA @ 1 Kay Siang Road',
          'start_date' => '2019-12-23',
          'start_time' => '16:00',
          'end_date' => '2019-12-23',
          'created_at' => '2019-12-03',
          'important' => true
        ],
        [
          'title' => '2019 Annual Fit and Proper Self-Declaration',
          'details' => '<p>Our company will be conducting the annual Fit &amp; Proper Declaration exercise on the 21st October 2019, Monday, 10am at our office premise.&nbsp;</p><p>This exercise is mandatory for individuals whose RNF was published before 1st October 2019. If you are one of them, please make yourself available on the date and time stated above. Attendance is compulsory.&nbsp;</p><p>&nbsp;</p><p>Please Note:&nbsp;</p><p>For those of you who have changes in your Business Interest(s)/Shareholding(s)/ Other Employment or if you have new Business Interest(s)/Shareholding(s)/ Other Employment, please bring a copy of the Business Profile from ACRA or overseas equivalent as at September 2019.&nbsp;</p><p>For some of you, we will email you directly to request for a copy of the Credit Bureau Reportsummary and you are required to bring this document on 21st October 2019.</p>',
          'location' => 'Legacy FA @ 1 Kay Siang Road',
          'start_date' => '2019-12-23',
          'start_time' => '16:00',
          'end_date' => '2019-12-23',
          'created_at' => '2019-10-03',
          'important' => true
        ],
      ];

      foreach ($sample_notices as $notice) {
        Notice::create($notice);
      }
    }
}
