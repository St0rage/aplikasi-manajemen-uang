<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $credentials;

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('simpanuang@gmail.com', 'Simpan Uang')
                    ->subject('Reset Password')
                    ->view('email.resetpassword')
                    ->with([
                        'email' => $this->credentials['email'],
                        'password' => $this->credentials['password']
                    ]);
    }
}
