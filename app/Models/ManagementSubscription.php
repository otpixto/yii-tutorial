<?php

namespace App\Models;

use Telegram\Bot\Exceptions\TelegramResponseException;

class ManagementSubscription extends BaseModel
{

    protected $table = 'managements_subscriptions';

    public static $name = 'Подписка на оповещения';

    public static $rules = [
        'management_id'         => 'required|integer',
        'telegram_id'           => 'required|integer',
        'first_name'            => 'nullable|integer',
        'last_name'             => 'nullable|integer',
        'username'              => 'required|string',
    ];

    protected $nullable = [
        'first_name',
        'last_name',
    ];

    protected $fillable = [
        'management_id',
        'telegram_id',
        'first_name',
        'last_name',
        'username',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function getName ()
    {
        $name = '';
        if ( ! empty( $this->first_name ) )
        {
            $name .= ' ' . $this->first_name;
        }
        if ( ! empty( $this->last_name ) )
        {
            $name .= ' ' . $this->last_name;
        }
        return trim( $name );
    }

    public function sendTelegram ( $message = null )
    {

        try
        {
            $response = \Telegram::sendMessage([
                'chat_id'                   => $this->telegram_id,
                'text'                      => $message,
                'parse_mode'                => 'html',
                'disable_web_page_preview'  => true
            ]);
            $chat = $response->getChat();
            if ( $chat )
            {
                $attributes = [
                    'first_name' => $chat->getFirstName() ?? null,
                    'last_name' => $chat->getLastName() ?? null,
                    'username' => $chat->getUsername()
                ];
                $this->edit( $attributes );
            }
            return true;
        }
        catch ( TelegramResponseException $e )
        {
            $errorData = $e->getResponseData();
            if ( $errorData['ok'] === false )
            {
                $this->addLog( 'Подписка прекращена по причине "' . $errorData['description'] . '"' );
                $this->delete();
            }
            return false;
        }

    }

}
