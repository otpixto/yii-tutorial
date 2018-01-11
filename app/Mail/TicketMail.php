<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct ( $message, $url = null )
    {
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build ()
    {
        return $this->markdown('emails.ticket' )
            ->with( 'message', $this->message )
            ->with( 'url', $this->url );
    }
}
