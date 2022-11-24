<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewValuationDoctorNotification extends Notification
{
    use Queueable;
    protected $user;
    protected $doctor;
    protected $valuation;
    protected $plan;
    protected $treatment;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $doctor, $valuation, $plan, $treatment)
    {
        $this->user = $user;
        $this->doctor = $doctor;
        $this->valuation = $valuation;
        $this->treatment = $treatment;
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
        return ['mail', 'database'];
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
            ->subject(config('app.name') . ' - ' . 'HAS SIDO ASIGNADO A UN NUEVO OBJETIVO')
            ->markdown('mails.new-valuation-notification-doctor', [
                'user' => $this->user,
                'plan' => $this->plan,
                'doctor' => $this->doctor,
                'valuation' => $this->valuation,
                'treatment' => $this->treatment,
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
            'link' => env('LINK_SHOW_ADMIN_VALORACION'),
            'title' => 'Tienes un nuevo objetivo',
            'description' => 'El paciente '.$this->user['name'].' '.$this->user['last_name'].' esta esperando que envies los recursos.'
        ];
    }
}
