<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValorationRequest;
use App\Models\AppointmentValuation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\SchedulesHoursMinute;
use App\Models\Subscription;
use App\Models\TypeTreatment;
use App\Models\Valuation;
use App\Notifications\NewScheduleDoctorNotification;
use App\Notifications\NewSchedulePatientNotification;
use App\Notifications\NewValuationDoctorNotification;
use App\Notifications\NewValuationPatientNotification;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MacsiDigital\Zoom\Facades\Zoom;

class ValorationController extends Controller
{
    public function getValorations(){
        $patient = Patient::where('user_id', auth()->id())->first();

        try {
            $valuations = Valuation::where('patient_id', $patient->id)->with('doctor', 'patient.user', 'treatment')->latest('created_at')->paginate(12);
            return response()->json([
                'success' => true,
                'message' => 'Get Valuations',
                'response' => 'get_valuations',
                'data' => $valuations,
                'lastPage' => $valuations->lastPage(),
                'total' => $valuations->total()
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET VALUATIONS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getValoration(Valuation $valuation){
        $getValuation = $valuation->wi->first();th('')
        return response()->json([
            'success' => true,
            'message' => 'Get Valuation',
            'response' => 'get_valuation',
            'data' => $valuation,
        ], 200);
    }

    public function createValoration(ValorationRequest $request)
    {
        DB::beginTransaction();
        $signature = null;
        $patient = Patient::where('user_id', auth()->user()->id)->with('user')->first();
        $doctorValoration =  Doctor::where('id', $request['doctorId'])->with('user')->first();
        $subscription = Subscription::where('id', $request['subscriptionId'])->with('plan')->first();
        $treatment = TypeTreatment::where('id', $request['selectedTreatment']['id'])->first();

        try {
            /*Creamos la firma en un formato válido y lo guardamos en el storage */
            if ($request['signature']) {
                $signature = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadSignaturePatientLocal($request['signature']['data']) : $this->uploadSignaturePatientStorage($request['signature']['data']);
            }
            /*Si el request viene con firma, le asignamos la firma y aceptamos los consentimientos*/
            if ($signature) {
                $patient->signature = $signature;
                $patient->consent_forms = 'accept';
                $patient->save();
            }

            $valuation = Valuation::create([
                'name' => $request['name'],
                'patient_id' => $patient->id,
                'slug' => Str::slug($request['name'].'-'.Str::random(10), '-'),
                'doctor_id' => $request['doctorId'],
                'type_treatment_id' => $request['selectedTreatment']['id'],
                'subscription_id' => $request['subscriptionId'],
                'objectives' => $request['objectives']
            ]);
            $appointmentDoctor = [];
            if ($request->appointments){
                foreach ($request->appointments as $appointment){
                    $doctorSchedule = DoctorSchedule::where('doctor_id', $appointment['doctor']['id'])->where('date',  $appointment['date'].' 00:00:00')->first();

                    if ($doctorSchedule){
                        /*Actualizamos los horario a no diponible o seleccionado*/
                        $scheduleHoursMinute = SchedulesHoursMinute::where('doctor_schedule_id',  $doctorSchedule->id)->where('hour', $appointment['onlyHour'])->where('minute', $appointment['onlyMinute'])->first();
                        $scheduleHoursMinute->state = 'SELECTED';
                        $scheduleHoursMinute->save();

                        $doctor =  Doctor::where('id', $appointment['doctor']['id'])->with('user')->first();
                        /* Válidamos las credenciales de acceso de zoom del doctor para poder crear reuniones*/
                        config(['zoom.api_key' => $doctor->zoom_api_key, 'zoom.api_secret' => $doctor->zoom_api_secret]);
                        /*Creamos la reunión en zoom*/
                        $startTime = Carbon::parse($appointment['date']. " " .$appointment['onlyHour'].":".$appointment['onlyMinute'].":00")->timezone('Europe/Madrid');
                        $zoomMeeting = Zoom::user()->find($doctor->user->email)
                            ->meetings()->create([
                                'topic' => 'Cita con el paciente ' .$patient->user->name.' '.$patient->user->last_name.' '.Str::random(5),
                                'duration' => 30, // In minutes, optional
                                'start_time' => $startTime,
                                'timezone' => config('app.timezone'),
                            ]);
                        /*Creamos la cita*/
                        $appointmentValuation = AppointmentValuation::create([
                            'valuation_id' =>  $valuation->id,
                            'date' => $appointment['date'].' 00:00:00',
                            'timezone' => $appointment['timezone'],
                            'only_hour' => $appointment['onlyHour'],
                            'only_minute' => $appointment['onlyMinute'],
                            'link_meeting' => $zoomMeeting->join_url
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
                    $subscription->plan,
                    $treatment->treatment,
                ));

                /*Notificamos al doctor de las citas creadas*/
                $doctorValoration->user->notify(new NewScheduleDoctorNotification(
                    $patient->user,
                    $doctorValoration->user,
                    $appointmentDoctor,
                    $subscription->plan,
                    $treatment->treatment
                ));
            }


            /*Notificamos al paciente de que ha creado una nueva valoració u objetivo*/
            $patient->user->notify(new NewValuationPatientNotification(
                $patient->user,
                $doctorValoration->user,
                $valuation->name,
                $subscription->plan,
                $treatment->treatment
            ));
            /*Notificamos al doctor que ha sido asignado a una nueva valoración u objetivo */
            $doctorValoration->user->notify(new NewValuationDoctorNotification(
                $patient->user,
                $doctorValoration->user,
                $valuation->name,
                $subscription->plan,
                $treatment->treatment
            ));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Create valoration',
                'response' => 'get_valoration',
                'data' => $valuation,

            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CREATE VALUATION.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function uploadSignaturePatientLocal($signature)
    {
        $randomNameSignature = 'signature-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
        Storage::disk('public')->put('/patient/signatures/' . $randomNameSignature, file_get_contents($signature));
        $urlFinal = '/storage/patient/signatures/' . $randomNameSignature;
        return $urlFinal;
    }

    public function uploadSignaturePatientStorage($signature)
    {

        $randomNameSignature = 'signature-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
        $path = Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $randomNameSignature, file_get_contents($signature), 'public');
        $urlFinal = env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $randomNameSignature;
        return $urlFinal;
    }

    public function uploadFiles(Request $request, $id)
    {
        $random = Str::random(10);
        $file = $request->file('file');
        $fileName = $random . '-' . $request->filename;
        $urlFinal = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadFilesLocal($file, $fileName) : $this->uploadFilesStorage($file, $fileName);
        Log::info($id);
        $patient = Patient::where('user_id', $id)->first();
        $valuation = $patient->valuations()->latest()->first();

        try {
            $valuation->archives()->firstOrCreate([
                'user_id' => $id,
                'path_file' => $urlFinal,
                'name_file' =>  $request->filename
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Upload Files',
                'response' => 'upload_file',
                'path_file' => $urlFinal,
                'name_file' => $request->filename

            ], 200);
        }catch (\Throwable $th){
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPLOAD FILE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }

    }

    public function uploadFilesStorage($file, $fileName)
    {
        $path = Storage::disk('digitalocean')->putFileAs(env('DIGITALOCEAN_FOLDER_ARCHIVES_PATIENT'), new File($file), str_replace(' ', '-', $fileName), 'public');
        return $path;
    }

    public function uploadFilesLocal($file, $fileName)
    {
        $path = Storage::disk('public')->put('/patient/archives/' . str_replace(' ', '-', $fileName), file_get_contents($file));
        $urlFinal = '/storage/patient/archives/' . $fileName;
        return $urlFinal;
    }
}
