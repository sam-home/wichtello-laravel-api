<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use User\Models\User;

class RegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected User $user, protected string $password)
    {
    }

    public function build(): Mailable
    {
        return $this->subject('Registrierung bei Wichtello.com')
            ->view('emails.register', [
                'user' => $this->user,
                'password' => $this->password
            ]);
    }
}
