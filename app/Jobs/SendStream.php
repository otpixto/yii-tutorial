<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketManagement;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $action;
    protected $object;
	
	public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( $action, $object )
    {
        try
        {
            $this->action = $action;
            $this->object = $object;
        }
        catch ( \Exception $e )
        {
			Log::critical( 'Exception', [ $e ] );
        }
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
                        'ticket_id' => $this->object->ticket->id ?? null,
                        'ticket_management_id' => $this->object->id
                    ];
                    break;
            }
            \Stream::send( $data );
        }
        catch ( \Exception $e )
        {
			Log::critical( 'Exception', [ $e ] );
        }
    }
	
	public function failed ( \Exception $e )
    {
        Log::critical( 'Exception', [ $e ] );
    }
	
}
