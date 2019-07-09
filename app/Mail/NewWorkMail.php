<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewWorkMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message;
    protected $url;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct ( $message, $url = null, $subject = '' )
    {
        $this->message = $message;
        $this->url = $url;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build ()
    {
        return $this->markdown( 'emails.ticket' )
            ->subject( $this->subject )
            ->with( 'message', $this->message )
            ->with( 'url', $this->url );
    }
}
