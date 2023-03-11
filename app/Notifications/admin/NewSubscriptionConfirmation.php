<?php

namespace App\Notifications\admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use PhpMqtt\Client\Facades\MQTT;

class NewSubscriptionConfirmation extends Notification
{
    use Queueable;
    protected $user;
    protected $plan;
    protected $subscription;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $plan, $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->plan = $plan;
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
        MQTT::publish('notification', 'new-subscription-confirmation');
        return [
            'link' => '/subscriptions',
            'title' => 'Nueva suscripción con el <strong>'.$this->plan->name.'</strong> ha sido adquirida. 🎊',
            'description' => 'El paciente es <strong>'.$this->user->name.' '.$this->user->last_name.'.Clic para más información.'
        ];
    }
}
