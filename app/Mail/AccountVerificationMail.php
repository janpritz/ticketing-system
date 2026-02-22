<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $verificationToken;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $verificationToken)
    {
        $this->userName = $userName;
        $this->verificationToken = $verificationToken;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Verify Your Account - Set Your Password')
            ->view('emails.account-verification')
            ->with([
                'userName' => $this->userName,
                'verificationToken' => $this->verificationToken,
            ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
