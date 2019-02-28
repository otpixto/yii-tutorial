<?php

namespace App\Jobs;

use App\Classes\Push;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $apiKey;
    protected $tokens;

    protected $message;
    protected $object;
    protected $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( $apiKey, $tokens = [], $message, $object = null, $id = null )
    {
        if ( ! is_array( $tokens ) )
        {
            $tokens = [ $tokens ];
        }
        $this->apiKey = $apiKey;
        $this->tokens = $tokens;
        $this->message = $message;
        $this->object = $object;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle ()
    {
        $client = new Push( $this->apiKey );
        $client
            ->setData( 'message', $this->message )
            ->setData( 'object', $this->object )
            ->setData( 'id', $this->id );
        foreach ( $this->tokens as $token )
        {
            if ( ! empty( $token ) )
            {
                $client->sendTo( $token );
            }
        }
    }

}
