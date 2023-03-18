<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

class NewMessageChatValoration extends Notification
{
    use Queueable;
    protected $valoration;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($valoration, $user)
    {
        $this->valoration = $valoration;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        Log::info($this->user);
        $notification =  [
            'link' => '/webapp/objetivos/'.$this->valoration->slug,
            'title' => 'El especialista te ha escrito al chat. 📩',
            'description' => 'Tienes un nuevo menseja para tu objetivo <strong>'.$this->valoration->name.'.</strong>'
        ];
        MQTT::publish('notification', $this->user);
        return $notification;
    }
}
