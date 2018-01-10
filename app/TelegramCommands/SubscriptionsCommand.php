<?php

namespace App\Telegram\Bot\Commands;

use App\Models\ManagementSubscription;
use Telegram\Bot\Commands\Command;

class SubscriptionsCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "subscriptions";

    /**
     * @var string Command Description
     */
    protected $description = "Список активных подписок";

    public function handle ( $arguments )
    {

        $telegram_id = $this->getUpdate()->getMessage()->getChat()->getId();

        $subs = ManagementSubscription
            ::where( 'telegram_id', '=', $telegram_id )
            ->get();

        if ( $subs->count() )
        {
            $text = '';
            foreach ( $subs as $i => $sub )
            {
                $text .= ( ++ $i ) . ' ' . $sub->management->name . PHP_EOL;
            }
        }
        else
        {
            $text = 'Активных подписок нет';
        }

        $this->replyWithMessage([
            'text' => $text,
            'reply_markup' => \Telegram::replyKeyboardHide()
        ]);

    }
}