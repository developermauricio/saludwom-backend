<?php

namespace App\Notifications\Patient;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmationSubscriptionNotification extends Notification
{
    use Queueable;
    protected $link;
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
        $this->link = '/webapp/valoracion/crear';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(config('app.name') . ' - ' . 'CONFIRMACIÓN DE SUSCRIPCIÓN')
            ->markdown('mails.confirmation-subscription', [
                'user' => $this->user,
                'plan' => $this->plan,
                'link' => $this->link,
                'subscription' => $this->subscription,
            ]);
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
            'link' => $this->link,
            'title' => 'Tienes una suscripción activa',
            'description' => 'Es momento de crear un nuevo objetivo 🤗.'
        ];
    }
}
