<?php

use App\Mails\Payroll\{PayrollStatement, PayrollStatementAdjustment, PayrollNull};
use App\Models\LegacyFA\Personnels\Personnel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () { abort(403); });

Route::middleware('shield')->prefix('emails')->group(function() {

    Route::get('data', function() {
        $personnel = Personnel::sn(1294);
        return $personnel->payroll_computations()->where('year',2019)->where('month','05')->count();

        $payroll_summary = [];
        $year = request()->input('year') ?? Carbon::now()->year;

        foreach(['01','02','03','04','05','06','07','08','09','10','11','12'] as $month) {
          $computation = $personnel->payroll_computations()->where('year', $year)->where('month', $month)->get();
          $payroll_summary[$month] = $personnel->payroll_era_summary($computation, 'lfa', $personnel->uuid);
        }

        return response()->json($personnel);
    });

    Route::get('payroll/statements/{year}/{month}/{personnel}', function($year, $month, $personnel){
        // $personnel = Personnel::whereUuid($personnel)->first();
        $personnel = Personnel::sn($personnel);
        // $personnel = Personnel::whereUuid($personnel)->first();
        if (!$personnel) return redirect('/emails/payroll/statement');
        $data = $personnel->payroll_statement($year, $month);
        // return PDF::loadView('emails.payroll.pdf', ['data' => $data])->inline();
        if ($data) {
            $pdf = PDF::loadView('emails.payroll.pdf', ['data' => $data])->output();
            $e = new PayrollStatement($data, $pdf);
            // Mail::to('xavier@black.sg')->send($e);
            // Mail::to('xavier@legacyfa-asia.com')->send($e);
            // Mail::to('jerry.chop@legacyfa-asia.com')->send($e);
            return ($e)->render();
        } else {
            return (new PayrollNull(['month' => $month, 'year' => $year, 'name' => $personnel->name]))->render();
        }
    });

    Route::get('payroll/adjusted/{year}/{month}/{personnel}', function($year, $month, $personnel){
        // $personnel = Personnel::whereUuid($personnel)->first();
        $personnel = Personnel::sn($personnel);
        // $personnel = Personnel::whereUuid($personnel)->first();
        if (!$personnel) return redirect('/emails/payroll/statement');
        $data = $personnel->payroll_statement($year, $month);
        // return PDF::loadView('emails.payroll.pdf', ['data' => $data])->inline();
        if ($data) {
            $pdf = PDF::loadView('emails.payroll.pdf', ['data' => $data])->output();
            $e = new PayrollStatementAdjustment($data, $pdf);
            // Mail::to('xavier@black.sg')->send($e);
            // Mail::to('xavier@legacyfa-asia.com')->send($e);
            // Mail::to('jerry.chop@legacyfa-asia.com')->send($e);
            return ($e)->render();
        } else {
            return (new PayrollNull(['month' => $month, 'year' => $year, 'name' => $personnel->name]))->render();
        }
    });
});