<?php

namespace App\Telegram\Bot\Commands;

use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var string Command Description
     */
    protected $description = 'Помощь';

    /**
     * {@inheritdoc}
     */
    public function handle ( $arguments )
    {

        $commands = $this->telegram->getCommands();

        $text = '';
        foreach ( $commands as $name => $handler )
        {
            $text .= sprintf('/%s - %s' . PHP_EOL, $name, $handler->getDescription() );
        }

        $this->replyWithMessage( compact('text' ) );
    }
}