<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MailResetPasswordNotification extends Notification
{
    use Queueable;

     /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if($notifiable->lang == 'en'){
            return (new MailMessage)
            ->subject('Reset Password Email')
            ->from('info@develop2you.com')
            ->greeting('Hello!')
            ->line('Please set a new password for your account by clicking the button below!')
            ->action('Set New Password', url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
            ->line('This link will expire in '.config('auth.passwords.users.expire').' minutes.')
            ->line('If you have not requested a password reset please ignore this email.')
            ->line('If you are having trouble clicking the button, please use the link below.')
            ->salutation('Thank you!');
        }

        if($notifiable->lang == 'pt'){
            return (new MailMessage)
            ->subject('Email de Redefinição de Password')
            ->from('info@develop2you.com')
            ->greeting(__('Olá!'))
            ->line('Por favor carregue no botão abaixo para redefinir a sua password!')
            ->action('Redefinir Password', url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
            ->line('Este link irá expirar em  '.config('auth.passwords.users.expire').' minutos.')
            ->line('Caso não tenha solicitado redefinição de password por favor ignore este email.')
            ->line('Se tiver problemas ao carregar no botão, por favor utilize o link abaixo.')
            ->salutation('Obrigado!');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
