<?php

namespace App\Notifications\Patient;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use PhpMqtt\Client\Facades\MQTT;

class SendResourcePatient extends Notification
{
    use Queueable;

    protected $user;
    protected $doctor;
    protected $valuation;
    protected $valuationSlug;
    protected $treatment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $doctor, $valuation, $valuationSlug, $treatment)
    {
        $this->user = $user;
        $this->doctor = $doctor;
        $this->valuation = $valuation;
        $this->treatment = $treatment;
        $this->valuationSlug = $valuationSlug;
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
            ->subject(config('app.name') . ' - ' . 'EL PACIENTE HA RESUELTO EL RECURSO')
            ->markdown('mails.new-resource-sent-by-patient', [
                'user' => $this->user,
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
            'link' => '/objetivos/' . $this->valuationSlug,
            'title' => 'Nuevo recurso resuelto para el tratamiento <strong>' . $this->valuation . ' 🔥</strong>',
            'description' => 'Revisa las respuestas del recurso y envia el tratamiento.'
        ];
        MQTT::publish('notification', $this->doctor['id']);
        return $notification;
    }
}
