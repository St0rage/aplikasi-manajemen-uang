<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAccount extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('daniyudistira25@gmail.com', 'Aplikasi Manajemen Uang')
                    ->subject('Akun Anda')
                    ->view('email.sendaccount')
                    ->with([
                        'email' => $this->account['email'],
                        'name' => $this->account['name'],
                        'password' => $this->account['password']
                    ]);
    }
}
