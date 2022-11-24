<?php

namespace App\Notifications;

use App\Helpers\ZoomHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use MacsiDigital\Zoom\Facades\Zoom;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class NewSchedulePatientNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $appointments;
    protected $plan;
    protected $treatment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $appointments, $plan, $treatment)
    {
        $this->user = $user;
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
        $email = (new MailMessage)
            ->subject(config('app.name') . ' - ' . 'CONFIRMACIÓN DE TU CITA');

        foreach ($this->appointments as $key => $appointment) {
            $calendar = Calendar::create()
                ->productIdentifier('descargar-agenda.cz')
                ->event(function (Event $event) use ($appointment, $key) {
                    $event->name('Tu cita #' . ($key + 1) . ' para tu tratamiento agendada para el ')
                        ->attendee($appointment->doctor['user']['email'])
                        ->startsAt(Carbon::parse($appointment->date . " " . $appointment->only_hour . ":" . $appointment->only_minute . ":00"));
                });
            $calendar->appendProperty(TextProperty::create('METHOD', 'REQUEST'));

            $email->attachData($calendar->get(), 'cita.ics', [
                'mime' => 'text/calendar; charset=UTF-8; method=REQUEST',
            ]);
        }



        $email->markdown('mails.new-schedule-notification-patient', [
            'user' => $this->user,
            'plan' => $this->plan,
            'appointments' => $this->appointments,
            'treatment' => $this->treatment,
//            'zoomMeeting' => $zoomMeeting->join_url
        ]);

//
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
        return [
            'link' => '',
            'title' => 'Tu cita ha sido agendada',
            'description' => ''
        ];
    }
}
