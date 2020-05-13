<?php

namespace App\Mails\Payroll;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PayrollStatement extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $attachment;
    public $subject = 'Your Payroll Statement for ';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $attachment = null)
    {
        $this->data = $data;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->subject . $this->data['payroll_month'] . ' ' . $this->data['payroll_year'];
        $email = $this->subject($subject)->replyTo($this->data['support_email'], "IT Helpdesk - Legacy FA")->markdown('emails.payroll.statement');

        if ($this->attachment) {
            return $email->attachData($this->attachment, $this->data['filename'], ['mime' => 'application/pdf']);
        } else {
            return $email;
        }
    }
}
