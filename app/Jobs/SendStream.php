<?php

namespace App\Jobs;

use App\Models\Ticket;
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
    protected $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( $action, $object )
    {
        $this->action = $action;
        $this->object = $object;
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
            switch ( get_class( $this->object ) )
            {
                case Ticket::class:
                    $data = [
                        'action' => $this->action,
                        'ticket_id' => $this->object->id
                    ];
                    break;
                case TicketManagement::class:
                    $data = [
                        'action' => $this->action,
                        'ticket_id' => $this->object->ticket->id,
                        'ticket_management_id' => $this->object->id
                    ];
                    break;
            }
            \Stream::send( $data );
        }
        catch ( \Exception $e )
        {

        }
    }
}
