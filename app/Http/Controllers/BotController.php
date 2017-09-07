<?php

namespace App\Http\Controllers;

use App\Models\Management;
use App\Models\ManagementSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Telegram\Bot\Api;

class BotController extends Controller
{

    public function __construct( Api $telegram )
    {
        $this->telegram = $telegram;
    }

    public function telegram ( Request $request, $token )
    {

        if ( $token != \Config::get( 'telegram.bot_token' ) ) return;

        $update = $this->telegram->commandsHandler( true );
        if ( ! $update->getMessage() ) return;

        $telegram_id = $update->getMessage()->getChat()->getId();

        if ( $update->getMessage()->getReplyToMessage() )
        {
            $message_id = $update->getMessage()->getReplyToMessage()->getMessageId();
            if ( \Cache::has( 'telegram-subscribe-' . $message_id ) && \Cache::get( 'telegram-subscribe-' . $message_id ) == $telegram_id )
            {
                $telegram_code = $update->getMessage()->getText();
                \Cache::forget( 'telegram-subscribe-' . $message_id );
                $res = Management::telegramSubscribe( $telegram_code, $telegram_id );
                if ( $res instanceof MessageBag )
                {
                    $text = $res->first();
                }
                else
                {
                    $text = 'Подписка успешно оформлена';
                }
                $this->telegram->sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => $text
                ]);
            }
            else if ( \Cache::has( 'telegram-unsubscribe-' . $message_id ) && \Cache::get( 'telegram-unsubscribe-' . $message_id ) == $telegram_id )
            {
                $telegram_code = $update->getMessage()->getText();
                \Cache::forget( 'telegram-unsubscribe-' . $message_id );
                $res = Management::telegramUnSubscribe( $telegram_code, $telegram_id );
                if ( $res instanceof MessageBag )
                {
                    $text = $res->first();
                }
                else
                {
                    $text = 'Подписка успешно отменена';
                }
                $this->telegram->sendMessage([
                    'chat_id' => $telegram_id,
                    'text' => $text
                ]);
            }
        }

        /*$this->telegram->sendChatAction([
            'chat_id' => $update->getMessage()->getChat()->getId(),
            'action' => 'typing'
        ]);

        $this->telegram->sendMessage([
            'chat_id' => $update->getMessage()->getChat()->getId(),
            'text' => $update->getMessage()->getText()
        ]);*/

    }

}
