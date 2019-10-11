<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Illuminate\Support\Facades\Log;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatIds;
    protected $message;
	
	public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( array $chatIds, $message )
    {
		try
		{
			$this->chatIds = array_unique( $chatIds );
			$this->message = trim( $message );
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
            if ( count( $this->chatIds ) && ! empty( $this->message ) )
            {
                $logs = new Logger( 'TELEGRAM' );
                $logs->pushHandler( new StreamHandler( storage_path( 'logs/telegram.log' ) ) );
                $logs->addInfo( 'Исходящее сообщение', [ $this->chatIds, $this->message ] );
                foreach ( $this->chatIds as $chatId )
                {
                    \Telegram::sendMessage([
                        'chat_id'                   => $chatId,
                        'text'                      => trim( $this->message ),
                        'parse_mode'                => 'html',
                        'disable_web_page_preview'  => true,
                        'reply_markup'              => \Telegram::replyKeyboardHide()
                    ]);
                }
            }
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
