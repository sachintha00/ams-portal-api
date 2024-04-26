<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
// use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserAuthenticationVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $activationCode;
    protected $activateUrl;
    
    public function __construct($activationCode, $activateUrl)
    {
        $this->activationCode = $activationCode;
        $this->activateUrl = $activateUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'User Authentication Verify Email',
        );
    }

    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'emails.userAuthenticationVerifyEmail',
    //     );
    // }

    public function build()
    {
        return $this->view('emails.userAuthenticationVerifyEmail')
                    ->with([
                        'token' => $this->activationCode,
                        'activateUrl' => $this->activateUrl,
                    ]);
    }

    public function attachments(): array
    {
        return [];
    }
}