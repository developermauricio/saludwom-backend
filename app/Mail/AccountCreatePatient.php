<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreatePatient extends Mailable
{
    use Queueable, SerializesModels;

    private $name;
    private $lastName;
    private $password;
    private $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $lastName, $password, $email)
    {
        $this->name = $name;
        $this->lastName = $lastName;
        $this->password = $password;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(config('app.name').'-'. 'CREDENCIALES DE ACCESO' )
            ->markdown('mails.patient-account-confirmation')
            ->with('name',$this->name)
            ->with('last_name',$this->lastName)
            ->with('password',$this->password)
            ->with('email',$this->email);
    }
}
