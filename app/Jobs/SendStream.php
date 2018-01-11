<?php

namespace App\Jobs;

use App\Models\TicketManagement;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $action;
    protected $ticketManagement;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( $action, TicketManagement $ticketManagement )
    {
        $this->action = $action;
        $this->ticketManagement = $ticketManagement;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle ()
    {
        try
        {
            \Stream::send( $this->action, $this->ticketManagement );
        }
        catch ( \Exception $e )
        {

        }
    }
}
