<?php

namespace App\Jobs;

use App\Mail\TicketMail;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $message;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( User $user, $message, $url = null )
    {
        $this->user = $user;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle ( Mailer $mailer )
    {

        $mailer
            ->to( $this->user )
            ->send( new TicketMail( $this->message, $this->url ) );

    }
}
