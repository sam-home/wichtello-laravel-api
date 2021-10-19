<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgetPasswordConfirmEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }


    public function build()
    {
        return $this->subject('Bestätigung von Passwort vergessen bei Wichtelo.com')
            ->view('emails.forget-password-confirm', [
                'user' => $this->user,
                'password' => $this->password
            ]);
    }
}
