<?php

namespace App\Notifications\doctor;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use PhpMqtt\Client\Facades\MQTT;

class NewValuationDoctorNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $doctor;
    protected $valuation;
    protected $valuationSlug;
    protected $plan;
    protected $treatment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $doctor, $valuation, $valuationSlug, $plan, $treatment)
    {
        $this->user = $user;
        $this->doctor = $doctor;
        $this->valuation = $valuation;
        $this->valuationSlug = $valuationSlug;
        $this->treatment = $treatment;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
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
                'valuationSlug' => $this->valuationSlug,
                'treatment' => $this->treatment,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $notification = [
            'link' => $this->valuationSlug,
            'title' => 'Ha sido asignad@ a un nuevo objetivo llamado <strong>'.$this->valuation.' 🔥</strong>.',
            'description' => 'El o La paciente <strong>' . $this->user['name'] . ' ' . $this->user['last_name'] . '</strong>, esta esperando que conozcas su objetivo y que envíes los recursos.'
        ];
        MQTT::publish('notification', $this->doctor['id']);
        return $notification;
    }
}
