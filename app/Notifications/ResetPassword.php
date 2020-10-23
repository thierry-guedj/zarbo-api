<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword as Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;


class ResetPassword extends Notification
{
    use Queueable;

    
    public function toMail($notifiable)
    {
        $url = url(config('app.client_url').'/password/reset/'.$this->token).
                    '?email='.urlencode($notifiable->email);

        return (new MailMessage)
                    ->line(Lang::getFromJson('You are receiving this email because we received a password reset request for your account.'))
                    ->action(Lang::getFromJson('Reset Password'), $url)
                    ->line(Lang::getFromJson('If you did not request a password reset, no further action is required.'));
    }

}
