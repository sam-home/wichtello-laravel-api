<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use User\Models\User;

class ForgetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected User $user)
    {
    }

    public function build(): Mailable
    {
        return $this->subject('Passwort vergessen bei Wichtello.com')
            ->view('emails.forget-password', [
                'passwordReset' => $this->user->reset
            ]);
    }
}
