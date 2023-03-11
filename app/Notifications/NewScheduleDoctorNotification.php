<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class NewScheduleDoctorNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $doctor;
    protected $appointments;
    protected $plan;
    protected $treatment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $doctor, $appointments, $plan, $treatment)
    {
        $this->user = $user;
        $this->doctor = $doctor;
        $this->appointments = $appointments;
        $this->plan = $plan;
        $this->treatment = $treatment;
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
        $subject = count($this->appointments) > 1 ? 'NUEVAS CITAS PROGRAMADAS' : 'NUEVA CITA PROGRAMADA';
        $email = (new MailMessage)
            ->subject(config('app.name') . ' - ' . $subject);

        foreach ($this->appointments as $key => $appointment) {
            $calendar = Calendar::create()
                ->productIdentifier(Str::random(5) . '-descargar-agenda.cz')
                ->event(function (Event $event) use ($appointment, $key) {
                    $event->name('Cita #' . ($key + 1) . ' para el tratamiento agendada para el ')
                        ->attendee($this->user['email'])
                        ->startsAt(Carbon::parse($appointment->date . " " . $appointment->only_hour . ":" . $appointment->only_minute . ":00"));
                });
            $calendar->appendProperty(TextProperty::create('METHOD', 'REQUEST'));

            $email->attachData($calendar->get(), 'cita.ics', [
                'mime' => 'text/calendar; charset=UTF-8; method=REQUEST',
            ]);
        }


        $email->markdown('mails.new-schedule-notification-doctor', [
            'user' => $this->user,
            'doctor' => $this->doctor,
            'plan' => $this->plan,
            'appointments' => $this->appointments,
            'treatment' => $this->treatment,

        ]);
        return $email;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $description = count($this->appointments) > 1 ? 'Han sido programadas varias citas con el paciente ' : 'Ha sido programada la cita con el paciente ';
        return [
            'link' => '/citas',
            'title' => count($this->appointments) > 1 ? 'Nuevas citas programadas. 🗓' : 'Nueva cita programada. 🗓',
            'description' => $description . '<strong>'.$this->user['name'] . ' ' . $this->user['last_name'].'</strong>.'
        ];
    }
}
