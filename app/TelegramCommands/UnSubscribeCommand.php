<?php

namespace Telegram\Commands;

use App\Models\ManagementSubscription;
use Telegram\Bot\Commands\Command;

class UnSubscribeCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "unsubscribe";

    /**
     * @var string Command Description
     */
    protected $description = "Отмена подписки на оповещения";

    public function handle ( $telegram_code )
    {

        $telegram_id = $this->getUpdate()->getMessage()->getChat()->getId();

        if ( empty( $telegram_code ) )
        {
            $response = $this->replyWithMessage([
                'text' => 'Введите пин-код, указанный в договоре',
                'reply_markup' => \Telegram::forceReply()
            ]);
            \Cache::put( 'telegram-unsubscribe-' . $response->getMessageId(), $telegram_id, 3 );
        }
        else
        {
            $sub = ManagementSubscription
                ::where( 'telegram_id', '=', $telegram_id )
                ->first();
            if ( $sub )
            {
                if ( $sub->management->telegram_code == $telegram_code )
                {
                    $sub->delete();
                    $text = 'Подписка успешно отменена';
                }
                else
                {
                    $text = 'Неверный пин-код';
                }
            }
            else
            {
                $text = 'Подписка не найдена';
            }
            $this->replyWithMessage([
                'text' => $text,
                'reply_markup' => \Telegram::replyKeyboardHide()
            ]);
        }

    }
}