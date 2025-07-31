<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Support\Facades\DB;


class MailInquireNotification extends Notification
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
        
        $inquires = DB::table('inquires')->select('inquires.*', 'products.name as product', 'products.code as code')
        ->leftJoin('products', 'inquires.product_id', '=', 'products.id')
        ->where('inquires.id', $this->inquire->id)->first();

        return (new MailMessage)
        ->subject('New Inquire!')
        ->from('info@develop2you.com')
        ->greeting('Hello')
        ->line('We receive a new inquire!')
        ->line('Name: '.  $inquires->name)
        ->line('Country: '.  $inquires->country)
        ->line('Email: '.  $inquires->email)
        ->line('Product: '.  $inquires->product)
        ->line('Message: '.  $inquires->description)
       
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
