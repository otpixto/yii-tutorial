<?php

namespace App\Jobs;

use App\Models\ManagementSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Telegram\Bot\Exceptions\TelegramResponseException;

class SendTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscription;
    protected $message;
	
	private $logs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( ManagementSubscription $subscription, $message )
    {
		try
		{
			$this->subscription = $subscription;
			$this->message = $message;
			$this->logs = new Logger( 'TELEGRAM' );
			$this->logs->pushHandler( new StreamHandler( storage_path( 'logs/telegram.log' ) ) );
			$this->logs->addInfo( 'Исходящее сообщение', [ $message, $subscription->toArray() ] );
		}
		catch ( \Exception $e )
		{
			
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
            $response = \Telegram::sendMessage([
                'chat_id'                   => $this->subscription->telegram_id,
                'text'                      => trim( $this->message ),
                'parse_mode'                => 'html',
                'disable_web_page_preview'  => true,
                'reply_markup'              => \Telegram::replyKeyboardHide()
            ]);
            $chat = $response->getChat();
            if ( $chat )
            {
                $attributes = [
                    'first_name' => $chat->getFirstName() ?? null,
                    'last_name' => $chat->getLastName() ?? null,
                    'username' => $chat->getUsername()
                ];
                $this->subscription->edit( $attributes );
            }
        }
        catch ( TelegramResponseException $e )
        {
            $errorData = $e->getResponseData();
            if ( $errorData[ 'ok' ] === false )
            {
                $this->subscription->addLog( 'Подписка прекращена по причине "' . $errorData[ 'description' ] . '"' );
                $this->subscription->delete();
            }
        }
		catch ( \Exception $e )
		{
			$this->logs->addCritical( 'Ошибка', $e );
		}
    }
}
