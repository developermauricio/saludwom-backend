<?php

namespace App\Notifications\Doctor;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Jenssegers\Date\Date;
use PhpMqtt\Client\Facades\MQTT;

class ConfirmationCancelAppointmentDoctor extends Notification
{
    use Queueable;
    protected $valuation;
    protected $user;
    protected $doctor;
    protected $appointment;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($valuation, $user, $doctor, $appointment)
    {
        $this->valuation = $valuation;
        $this->user = $user;
        $this->doctor = $doctor;
        $this->appointment = $appointment;
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
        $subject = 'CONFIRMACIÓN DE CITA CANCELADA';
        $email = (new MailMessage)
            ->subject(config('app.name') . ' - ' . $subject);


        $email->markdown('mails.confirmation-cancel-appointment-doctor', [
            'valuation' => $this->valuation,
            'user' => $this->user,
            'doctor' => $this->doctor,
            'appointment' => $this->appointment,
        ]);
        return $email;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        MQTT::publish('notification', 'confirmation-cancel-appointment-doctor');
        return [
            'link' => '',
            'title' => 'Tu cita para el <strong>'.Date::parse(Carbon::parse($this->appointment['date'])->timezone($this->appointment['timezone']))->locale('es')->format('l F d Y H:i:s').'</strong> con el paciente <strong>'.$this->user['name'].' '.$this->user['last_name'].'</strong> ha sido cancelada.',
            'description' => 'Puede ser que el paciente reprograme su cita pronto. 😊'
        ];
    }
}
