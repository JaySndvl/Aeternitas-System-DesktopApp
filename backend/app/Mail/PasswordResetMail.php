<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetToken;
    public $userName;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($resetToken, $userName)
    {
        $this->resetToken = $resetToken;
        $this->userName = $userName;
        $this->resetUrl = config('app.url') . '/reset-password?token=' . $resetToken;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reset Your Password - ' . config('app.name'))
                    ->view('emails.password-reset');
    }
}
