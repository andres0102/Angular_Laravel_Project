<?php

namespace App\Mails\Payroll;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PayrollNull extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $month;
    public $year;
    public $subject = 'Payroll statement unavailable.';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->name = $data['name'];
        $this->month = $data['month'];
        $this->year = $data['year'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->subject;
        $email = $this->subject($subject)->markdown('emails.payroll.null');

        return $email;
    }
}
