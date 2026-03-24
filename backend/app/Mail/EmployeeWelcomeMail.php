<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $password;
    public $account;

    /**
     * Create a new message instance.
     */
    public function __construct($employee, $password, $account)
    {
        $this->employee = $employee;
        $this->password = $password;
        $this->account = $account;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . ' - Your Account Details')
                    ->view('emails.welcome');
    }
}
