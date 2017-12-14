<?php

namespace Telegram\Commands;

use App\Models\Management;
use Illuminate\Support\MessageBag;
use Telegram\Bot\Commands\Command;

class SubscribeCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "subscribe";

    /**
     * @var string Command Description
     */
    protected $description = "Подписка на оповещения";

    public function handle( $telegram_code )
    {

        $message = $this->getUpdate()->getMessage();
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

        if ( empty( $telegram_code ) )
        {
            $response = $this->replyWithMessage([
                'text' => 'Введите пин-код, указанный в договоре',
                'reply_markup' => \Telegram::forceReply()
            ]);
            \Cache::put( 'telegram-subscribe-' . $response->getMessageId(), $telegram_id, 3 );
        }
        else
        {
            $res = Management::telegramSubscribe( $telegram_code, $attributes );
            if ( $res instanceof MessageBag )
            {
                $text = $res->first();
            }
            else
            {
                $text = 'Подписка успешно оформлена';
            }
            $this->replyWithMessage([
                'text' => $text,
                'reply_markup' => \Telegram::replyKeyboardHide()
            ]);
        }

    }
}