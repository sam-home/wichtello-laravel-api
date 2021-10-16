<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegisterJoinEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $password;
    protected $confirm;

    public function __construct($user, $password, $confirm)
    {
        $this->user = $user;
        $this->password = $password;
        $this->confirm = $confirm;
    }

    public function build()
    {
        return $this->subject('Registrierung und Gruppenbeitritt bei Wichtello.com')->view('emails.register-join', ['user' => $this->user, 'password' => $this->password, 'confirm' => $this->confirm]);
    }
}
