<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Support\Facades\DB;


class MailUserNotification extends Notification
{
    use Queueable;

     /**
     * The password reset token.
     *
     * @var string
     */
    public $inquire;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($inquire)
    {
        $this->inquire = $inquire;
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
    
        return (new MailMessage)
        ->subject('New Inquire!')
        ->from('app@develop2you.com')
        ->greeting('Hello')
        ->line('New user available!')
        ->line('Name: '.  $notifiable->name)
        ->line('Email: '.  $notifiable->email)
       
        ->salutation('Best regards,');
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
