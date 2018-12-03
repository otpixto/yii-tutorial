<?php

namespace App\Http\Controllers;

use App\Models\Management;
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
        $message = $update->getMessage();
        if ( ! $message ) return;
        $chat = $message->getChat();
        if ( ! $chat ) return;

        $telegram_id = $chat->getId();

        $attributes = [
            'telegram_id' => $telegram_id,
            'first_name' => $chat->getFirstName() ?? null,
            'last_name' => $chat->getLastName() ?? null,
            'username' => $chat->getUsername()
        ];

        $reply = $message->getReplyToMessage();

        if ( $reply )
        {
            $message_id = $reply->getMessageId();
            $telegram_code = $message->getText();
            if ( \Cache::has( 'telegram-subscribe-' . $message_id ) && \Cache::get( 'telegram-subscribe-' . $message_id ) == $telegram_id )
            {
                \Cache::forget( 'telegram-subscribe-' . $message_id );
                $res = Management::telegramSubscribe( $telegram_code, $attributes );
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
                \Cache::forget( 'telegram-unsubscribe-' . $message_id );
                $res = Management::telegramUnSubscribe( $telegram_code, $attributes );
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
