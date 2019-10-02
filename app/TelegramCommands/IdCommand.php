<?php

namespace App\Telegram\Bot\Commands;

use Telegram\Bot\Commands\Command;

class IdCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "id";

    /**
     * @var string Command Description
     */
    protected $description = "ID текущего чата";

    public function handle ()
    {

        $message = $this->getUpdate()->getMessage();
        if ( ! $message ) return;
        $chat = $message->getChat();
        if ( ! $chat ) return;

        $telegram_id = $chat->getId();

        $this->replyWithMessage([
            'text' => 'ID текущего чата: ' . $telegram_id,
            'reply_markup' => \Telegram::replyKeyboardHide()
        ]);

    }
}