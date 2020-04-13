<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MailResetPasswordToken extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct ( $token )
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via ( $notifiable )
    {
        return [ 'mail' ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail ( $notifiable )
    {
        return ( new MailMessage )
            ->subject('Сброс пароля' )
            ->greeting('Здравствуйте, ' . $notifiable->getName() . '!' )
            ->line('Вы получили это электронное письмо потому что мы получили запрос на сброс пароля для вашей учетной записи.')
            ->action('Сбросить пароль', route('reset', $this->token ) . '?email=' . $notifiable->email )
            ->line('Если вы не запрашивали сброс пароля, никаких дополнительных действий не требуется.' );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray ( $notifiable )
    {
        return [
            //
        ];
    }
}
