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
    protected $title;
    protected $body;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( $apiKey, $tokens = [], $title, $body, $data = [] )
    {
        if ( ! is_array( $tokens ) )
        {
            $tokens = [ $tokens ];
        }
        if ( ! is_array( $data ) )
        {
            $data = [ $data ];
        }
        $this->apiKey = $apiKey;
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
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
            ->setTitle( $this->title )
            ->setBody( $this->body );
        foreach ( $this->data as $key => $value )
        {
            $client
                ->setData( $key, $value );
        }
        foreach ( $this->tokens as $token )
        {
            if ( ! empty( $token ) )
            {
                $client->sendTo( $token );
            }
        }
    }

}
