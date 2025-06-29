<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $formasi;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $formasi)
    {
        $this->name = $name;
        $this->formasi = $formasi;
    }

    public function build()
    {
        return $this->subject('Undangan Uji Kemampuan Teknis - AMDALNET')
                    ->view('emails.user_notification');
    }
}
