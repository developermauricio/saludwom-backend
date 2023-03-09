<?php

namespace App\Notifications\Patient;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConfirmationCancelAppointmentPatient extends Notification
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


        $email->markdown('mails.confirmation-cancel-appointment-patient', [
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
        return [
            'link' => '',
            'title' => 'Tu cita ha sido cancelada.',
            'description' => ''
        ];
    }
}
