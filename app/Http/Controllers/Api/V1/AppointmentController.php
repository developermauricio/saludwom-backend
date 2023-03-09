<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Models\AppointmentValuation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\SchedulesHoursMinute;
use App\Models\Valuation;
use App\Notifications\Doctor\ConfirmationCancelAppointmentDoctor;
use App\Notifications\NewScheduleDoctorNotification;
use App\Notifications\NewSchedulePatientNotification;
use App\Notifications\Patient\ConfirmationCancelAppointmentPatient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MacsiDigital\Zoom\Facades\Zoom;

class AppointmentController extends Controller
{
    public function cancelAppointment($idAppointmentValuation)
    {
        $appointmentValuation = AppointmentValuation::where('id', $idAppointmentValuation)->first();
        $doctor = Doctor::where('id', $appointmentValuation->doctor_id)->with('user')->first();
        $valutionPatient = Valuation::where('id', $appointmentValuation->valuation_id)->with('patient.user', 'treatment')->first();
        DB::beginTransaction();
        try {
            /* Cancelamos la cita*/
            $appointmentValuation->state = AppointmentValuation::CANCELLED; //Cancelamos la cita cambiando el estado
            $appointmentValuation->save();
            /* Volvemos disponible la hora*/
            $scheduleHoursMinute = SchedulesHoursMinute::where('id', $appointmentValuation->schedule_hours_minutes_id)->first();
            $scheduleHoursMinute->state = 'AVAILABLE';
            $scheduleHoursMinute->save();

            /* Cancelamos o eliminamos la reunión de ZOOM*/
            /* Válidamos las credenciales de acceso de zoom del doctor*/
            config(['zoom.api_key' => $doctor->zoom_api_key, 'zoom.api_secret' => $doctor->zoom_api_secret]);
            $appoitmentZoom = Zoom::meeting()->find($appointmentValuation->id_meeting_zoom); //Eliminamos la reunión de zoom
            if ($appoitmentZoom) {
                $appoitmentZoom->delete();
            }
            /*Notificamos al paciente de la cancelación de la cita*/
            $valutionPatient->patient->user->notify(new ConfirmationCancelAppointmentPatient(
                $valutionPatient,
                $valutionPatient->patient->user,
                $doctor,
                $appointmentValuation,
            ));
            $doctor->user->notify(new ConfirmationCancelAppointmentDoctor(
                $valutionPatient,
                $valutionPatient->patient->user,
                $doctor,
                $appointmentValuation,
            ));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cancel appointment',
                'response' => 'cancel_appointment',
                'data' => $appointmentValuation,

            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CANCEL APPOINTMENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function rescheduleAppointment(RescheduleAppointmentRequest $request)
    {
        DB::beginTransaction();
        $patient = Patient::where('user_id', auth()->user()->id)->with('user')->first();
        try {
            if ($request['appointments']) {
                $dateNotAvailable = false;
                foreach ($request['appointments'] as $appointment) {
                    $doctorSchedule = DoctorSchedule::where('doctor_id', $appointment['doctor']['id'])->where('date', $appointment['date'] . ' 00:00:00')->first();
                    if ($doctorSchedule) {
                        /*Actualizamos los horarios a no diponible o seleccionado*/
                        $scheduleHoursMinute = SchedulesHoursMinute::where('doctor_schedule_id', $doctorSchedule->id)->where('hour', $appointment['onlyHour'])->where('minute', $appointment['onlyMinute'])->first();
                        $scheduleHoursMinute->state = 'SELECTED';
                        $scheduleHoursMinute->save();

                        /*Validamos si la fecha tiene horas disponibles*/
                        $timesSchedule = SchedulesHoursMinute::where('doctor_schedule_id', $doctorSchedule->id)->get();
                        foreach ($timesSchedule as $timeSchedule) {
                            if ($timeSchedule->state === 'AVAILABLE') {
                                $dateNotAvailable = true;
                            }
                        }
                        Log::info($dateNotAvailable);
                        /* Si la fecha global no tiene horas disponibles,entonces pasa a no disponible la fecha global*/
                        if (!$dateNotAvailable) {
                            $doctorSchedule->state = 'COMPLETED';
                            $doctorSchedule->save();
                        }
                        $doctor = Doctor::where('id', $appointment['doctor']['id'])->with('user')->first();
                        /* Válidamos las credenciales de acceso de zoom del doctor para poder crear reuniones*/
                        config(['zoom.api_key' => $doctor->zoom_api_key, 'zoom.api_secret' => $doctor->zoom_api_secret]);
                        /*Creamos la reunión en zoom*/
                        $startTime = Carbon::parse($appointment['date'] . " " . $appointment['onlyHour'] . ":" . $appointment['onlyMinute'] . ":00")->timezone('Europe/Madrid');
                        $zoomMeeting = Zoom::user()->find($doctor->user->email)
                            ->meetings()->create([
                                'topic' => 'Cita con el paciente ' . auth()->user()->name . ' ' . auth()->user()->last_name . ' ' . Str::random(5),
                                'duration' => 30, // In minutes, optional
                                'start_time' => $startTime,
                                'timezone' => config('app.timezone'),
                            ]);
                        /*Creamos la cita*/
                        $appointmentValuation = AppointmentValuation::create([
                            'valuation_id' => $request['valuation_id'],
                            'doctor_id' => $doctor->id,
                            'schedule_hours_minutes_id' => $scheduleHoursMinute->id,
                            'date' => $appointment['date'] . ' ' . $appointment['onlyHour'] . ':' . $appointment['onlyMinute'] . ':00',
                            'only_date' => $appointment['date'],
                            'timezone' => $appointment['timezone'],
                            'only_hour' => $appointment['onlyHour'],
                            'only_minute' => $appointment['onlyMinute'],
                            'link_meeting_zoom' => $zoomMeeting->join_url,
                            'id_meeting_zoom' => $zoomMeeting->id,
                        ]);
                        /*Creamos el objeto que enviaremos al correo electrónico*/
                        $appointmentDoctor[] = (object)[
                            'doctor' => $doctor,
                            'date' => $appointment['date'],
                            'timezone' => $appointment['timezone'],
                            'only_hour' => $appointmentValuation->only_hour,
                            'only_minute' => $appointmentValuation->only_minute,
                            'link_meeting' => $zoomMeeting->join_url
                        ];

                    }
                }
                /*Notificamos al paciente de las citas creadas*/
                $patient->user->notify(new NewSchedulePatientNotification(
                    $patient->user,
                    $appointmentDoctor,
                    $request['subscription']['plan'],
                    $request['treatment']['treatment'],
                ));

                /*Notificamos al doctor de las citas creadas*/
                $doctor->user->notify(new NewScheduleDoctorNotification(
                    $patient->user,
                    $doctor->user,
                    $appointmentDoctor,
                    $request['subscription']['plan'],
                    $request['treatment']['treatment']
                ));
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Create Appointment',
                'response' => 'create_appointment',
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR RESCHEDULE APPOINTMENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }
}
