<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('パスワードリセットのお知らせ - Shadova Log')
            ->greeting('こんにちは！')
            ->line('パスワードリセットのリクエストを受け付けました。')
            ->action('パスワードをリセット', $url)
            ->line('このリンクは60分間有効です。')
            ->line('パスワードリセットをリクエストしていない場合は、このメールを無視してください。')
            ->salutation('Shadova Log');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
