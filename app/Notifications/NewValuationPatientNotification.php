<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

class NewValuationPatientNotification extends Notification
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
        $this->treatment = $treatment;
        $this->valuationSlug = $valuationSlug;
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
            ->subject(config('app.name') . ' - ' . 'CONFIRMACIÓN DE UN NUEVO OBJETIVO CREADO')
            ->markdown('mails.new-valuation-notification-patient', [
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
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $notification = [
            'link' => '/webapp/objetivos/'.$this->valuationSlug,
            'title' => 'Has creado un nuevo objetivo llamado <strong>'.$this->valuation.' 🔥</strong>',
            'description' => 'Pronto el especialista enviará los recursos para iniciar el tratamiento.'
        ];
        MQTT::publish('notification', 'new-valuation-patient-notification');
        return $notification;
    }
}
