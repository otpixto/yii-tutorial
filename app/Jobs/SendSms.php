<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $number;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( $number, $message )
    {
        $this->number = $number;
        $this->message = $message;
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
            $url = config( 'sms.url_full' );
            $url .= '&to=' . $this->getNumber();
            $url .= '&text=' . urlencode( $this->message );
            file_get_contents( $url );
        }
        catch ( \Exception $e )
        {

        }
    }

    private function getNumber ()
    {
        return '7' . mb_substr( preg_replace( '/\D/', '', $this->number ), -10 );
    }

}
