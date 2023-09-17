<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValorationRequest;
use App\Http\Resources\ValorationResource;
use App\Models\AnswerQuestionResource;
use App\Models\AppointmentValuation;
use App\Models\ChatUserValuation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\QuestionsQuestionnaire;
use App\Models\Resource;
use App\Models\SchedulesHoursMinute;
use App\Models\Subscription;
use App\Models\TypeTreatment;
use App\Models\User;
use App\Models\Valuation;
use App\Notifications\Doctor\NewValuationDoctorNotification;
use App\Notifications\NewScheduleDoctorNotification;
use App\Notifications\NewSchedulePatientNotification;
use App\Notifications\NewValuationPatientNotification;
use App\Notifications\Patient\ResourceSentNotification;
use App\Notifications\Patient\SendResourcePatient;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;
use MacsiDigital\Zoom\Facades\Zoom;

class ValorationController extends Controller
{
    public function getValorations()
    {
        $patient = Patient::where('user_id', auth()->id())->first();

        try {
            $valuations = Valuation::where('patient_id', $patient->id)->with('doctor', 'patient.user', 'treatment', 'appointments.doctor.user')->latest('created_at')->paginate(8);
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

    public function getValorationsAdmin(Request $request, $dateFilter)
    {
        $date = json_decode($dateFilter);

        try {
            if ($request->doctor) {
                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])
                        ->whereHas('doctor', function ($q) use ($request) {
                            $q->where('doctor_id', $request->doctor);
                        })
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::whereHas('doctor', function ($q) use ($request) {
                        $q->where('doctor_id',$request->doctor);
                    })->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );
            }

            if ($request->plan) {

                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])
                        ->whereHas('subscription', function ($q) use ($request) {
                            $q->where('plan_id', $request->plan);
                        })
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();

                } else {

                    $valorations = Valuation::whereHas('subscription', function ($q) use ($request) {
                            $q->where('plan_id', $request->plan);
                        })->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );
            }
            if ($request->genderId) {

                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();

                } else {
                    $valorations = Valuation::whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );


            }
            if ($request->state) {
//                $valorations = ValorationResource::collection(
//                    Valuation::where('doctor_id', $doctor->id)
//                        ->whereHas('patient', function ($q) use ($request) {
//                            $q->where('patient_type', );
//                        })->orderByDesc('created_at')->get());
                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])
                        ->where('state', $request->state)->with('patient')
                        ->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::where('state', $request->state)->with('patient')
                        ->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );
            }
            if ($request->genderId && $request->state && $request->plan && $request->doctor) {
//                $valorations = ValorationResource::collection(
//                    Valuation::where('doctor_id', $doctor->id)
//                        ->whereHas('patient', function ($q) use ($request) {
//                            $q->where('gender_id', $request->genderId)->where('patient_type', $request->type);
//                        })->orderByDesc('created_at')->get());
                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])
                        ->where('state', $request->state)
                        ->whereHas('subscription', function ($q) use ($request) {
                            $q->where('plan_id', $request->plan);
                        })->whereHas('doctor', function ($q) use ($request) {
                            $q->where('doctor_id', $request->doctor);
                        })->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::where('state', $request->state)
                        ->whereHas('subscription', function ($q) use ($request) {
                            $q->where('plan_id', $request->plan);
                        })
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );
            }

            if ($request->genderId == null && $request->state == null && $request->plan == null && $request->doctor == null) {

                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])
                        ->with(['patient.user.identificationType', 'patient.gender',
                            'patient.user.city', 'patient.user.country', 'subscription.plan', 'doctor.user'
                        ])->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::with(['patient.user.identificationType', 'patient.gender',
                            'patient.user.city', 'patient.user.country', 'subscription.plan', 'doctor.user'
                        ])->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );
            }
//            $countGenderM = $valorations->filter(function ($item, $key) {
//                return $item->patient->gender_id === 1;
//            });
//            $countGenderF = $patients->filter(function ($item, $key) {
//                return $item->patient->gender_id === 2;
//            });
//            $countGenderO = $patients->filter(function ($item, $key) {
//                return $item->patient->gender_id === 3;
//            });
//            $countTypeClient = $patients->filter(function ($item, $key){
//                return $item->patient->patient_type === 'client';
//            });
//            $countTypeCourtesy = $patients->filter(function ($item, $key){
//                return $item->patient->patient_type === 'courtesy';
//            });
            $countStatePendSendReso = $valorations->filter(function ($item, $key) {
                return $item->state === '1';
            });
            $countStateResoSedFromDoctor = $valorations->filter(function ($item, $key) {
                return $item->state === '2';
            });
            $countStatePendSendTreaFromDoctor = $valorations->filter(function ($item, $key) {
                return $item->state === '3';
            });
            $countStateInTreatment = $valorations->filter(function ($item, $key) {
                return $item->state === '4';
            });
            $countStateFinished = $valorations->filter(function ($item, $key) {
                return $item->state === '5';
            });

            $totalValorations = new \stdClass();
            $totalValorations->total = $valorations->count();
            $totalValorations->type = 'totalValorations';

            $totalPendSendRes = new \stdClass();
            $totalPendSendRes->total = $countStatePendSendReso->count();
            $totalPendSendRes->type = 'totalPendSendRes';

            $totalResSendFromDoctor = new \stdClass();
            $totalResSendFromDoctor->total = $countStateResoSedFromDoctor->count();
            $totalResSendFromDoctor->type = 'totalResSendFromDoctor';

            $totalPendSendTreaFromDoctor = new \stdClass();
            $totalPendSendTreaFromDoctor->total = $countStatePendSendTreaFromDoctor->count();
            $totalPendSendTreaFromDoctor->type = 'totalPendSendTreaFromDoctor';

            $totalInTreatment = new \stdClass();
            $totalInTreatment->total = $countStateInTreatment->count();
            $totalInTreatment->type = 'totalInTreatment';

            $totalFinished = new \stdClass();
            $totalFinished->total = $countStateFinished->count();
            $totalFinished->type = 'totalFinished';

//            $totalPatientsM = new \stdClass();
//            $totalPatientsM->total = $countGenderM->count();
//            $totalPatientsM->type = 'totalPatientsM';
//
//            $totalPatientsF = new \stdClass();
//            $totalPatientsF->total = $countGenderF->count();
//            $totalPatientsF->type = 'totalPatientsF';
//
//            $totalPatientsO = new \stdClass();
//            $totalPatientsO->total = $countGenderO->count();
//            $totalPatientsO->type = 'totalPatientsO';

//            $totalPatientsTypeCli = new \stdClass();
//            $totalPatientsTypeCli->total = $countTypeClient->count();
//            $totalPatientsTypeCli->type = 'totalPatientsCli';
//
//            $totalPatientsTypeCour = new \stdClass();
//            $totalPatientsTypeCour->total = $countTypeCourtesy->count();
//            $totalPatientsTypeCour->type = 'totalPatientsCour';

            Log::info(json_encode($valorations));
            return response()->json([
                'success' => true,
                'total' => $valorations->count(),
                'countDataState' => [
                    $totalValorations,
                    $totalPendSendRes,
                    $totalResSendFromDoctor,
                    $totalPendSendTreaFromDoctor,
                    $totalInTreatment,
                    $totalFinished
//                    $totalPatientsM,
//                    $totalPatientsF,
//                    $totalPatientsO,
//                    $totalPatientsTypeCli,
//                    $totalPatientsTypeCour
                ],

                'message' => 'Get Valorations',
                'response' => 'get_valorations',
                'data' => $valorations,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET PATIENTS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getValoration($valuation)
    {
        $getValuation = Valuation::where('slug', $valuation)->with('doctor.user', 'patient.user.country', 'patient.gender', 'treatment', 'subscription.plan', 'archives', 'appointments.doctor.user', 'chat')->first();
        $getValuation->patient->user->setAttribute('age', Carbon::parse($getValuation->patient->user->birthday)->age);
        $getValuation->setAttribute('created_at_format', Date::parse($getValuation->created_at)->locale('es')->format('l d F Y'));
        $getValuation->patient->user->setAttribute('country_flag', $getValuation->patient->user->country ? $getValuation->patient->user->country->flag : '');

        return response()->json([
            'success' => true,
            'message' => 'Get Valuation',
            'response' => 'get_valuation',
            'data' => $getValuation,
        ], 200);
    }

    public function createValoration(ValorationRequest $request)
    {
        DB::beginTransaction();
        $signature = null;
        $patient = Patient::where('user_id', auth()->user()->id)->with('user')->first();
        $doctorValoration = Doctor::where('id', $request['doctorId'])->with('user')->first();
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
                'slug' => Str::slug($request['name'] . '-' . Str::random(10), '-'),
                'doctor_id' => $request['doctorId'],
                'type_treatment_id' => $request['selectedTreatment']['id'],
                'subscription_id' => $request['subscriptionId'],
                'objectives' => $request['objectives']
            ]);

            /*Creamos el chat si es plan diamante o esmeralda*/
            if ($subscription->plan->id === 1 || $subscription->plan->id === 2) {
                $usersChat = [$doctorValoration->user->id, $patient->user->id];
                $chatChannel = $valuation->chat()->firstOrCreate([
                    'chat_key' => 'chat-' . Str::random(8),
                ]);
                foreach ($usersChat as $user) {
                    $chatUserValoration = ChatUserValuation::create([
                        'chat_channel_id' => $chatChannel->id,
                        'user_id' => $user
                    ]);
                }

            }
            $appointmentDoctor = [];

            if ($request->appointments) {
                $dateNotAvailable = false;
                foreach ($request->appointments as $appointment) {
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
                                'topic' => 'Cita con el paciente ' . $patient->user->name . ' ' . $patient->user->last_name . ' ' . Str::random(5),
                                'duration' => 30, // In minutes, optional
                                'start_time' => $startTime,
                                'timezone' => config('app.timezone'),
                            ]);
                        /*Creamos la cita*/
                        $appointmentValuation = AppointmentValuation::create([
                            'valuation_id' => $valuation->id,
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
                $valuation->slug,
                $subscription->plan,
                $treatment->treatment
            ));
            /*Notificamos al doctor que ha sido asignado a una nueva valoración u objetivo */
            $doctorValoration->user->notify(new NewValuationDoctorNotification(
                $patient->user,
                $doctorValoration->user,
                $valuation->name,
                $valuation->slug,
                $subscription->plan,
                $treatment->treatment
            ));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Create valoration',
                'response' => 'create_valoration',
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

    public function uploadFiles(Request $request, $id, $valuationId)
    {
        $random = Str::random(10);
        $file = $request->file('file');
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = $random . '-' . $request->filename;
        Log::info($request->file('file'));
        $urlFinal = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadFilesLocal($file, $fileName) : $this->uploadFilesStorage($file, $fileName);
        $storage = env('FILES_UPLOAD_PRODUCTION') === false ? 'local' : 'cloud';

        if ($valuationId !== '0') {
            $valuation = Valuation::find($valuationId);
        } else {
            $patient = Patient::where('user_id', $id)->first();
            $valuation = $patient->valuations()->latest()->first();
        }
        DB::beginTransaction();
        try {
            $valuation->archives()->firstOrCreate([
                'user_id' => $id,
                'type_file' => strtolower($fileExtension),
                'path_file' => '/' . $urlFinal,
                'name_file' => $request->filename,
                'storage' => $storage
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Upload Files',
                'response' => 'upload_file',
                'path_file' => $urlFinal,
                'name_file' => $request->filename

            ], 200);
        } catch (\Throwable $th) {
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
        $fileNameStr = str_replace(' ', '-', $fileName);
        Log::info($file);
        $path = Storage::disk('public')->put('/patient/archives/' . $fileNameStr, file_get_contents($file));
        $urlFinal = 'storage/patient/archives/' . $fileNameStr;
        return $urlFinal;
    }

    public function removeArchive(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('archives')
                ->where('id', $request['id'])
                ->delete();
            env('FILES_UPLOAD_PRODUCTION') === false ? $this->removeArchiveLocal($request) : $this->removeArchiveStorage($request);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Remove File(s)',
                'response' => 'remove_files',
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR REMOVE FILE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function removeArchiveLocal($request)
    {
        if ($request) {
            $pathInfo = pathinfo($request['path']);
            Storage::delete('public/patient/archives/' . $pathInfo['basename']);
        }
    }

    public function removeArchiveStorage($request)
    {
        if ($request) {
            $pathInfo = pathinfo($request['path']);
            Storage::disk('digitalocean')->delete(env('DIGITALOCEAN_FOLDER_ARCHIVES_PATIENT') . '/' . $pathInfo['basename']);
        }
    }

    public function updateValorationObjective(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $valuation = Valuation::find($id);
            if ($request['valuationName'] !== $valuation->name) {
                $valuation->slug = Str::slug($request['valuationName'] . '-' . Str::random(10), '-');
            }
            $valuation->name = $request['valuationName'];
            $valuation->objectives = $request['valuationObjective'];
            $valuation->type_treatment_id = $request['valuationTreatment']['id'];
            $valuation->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Update Objective',
                'response' => 'put_update_objective',
                'data' => $valuation,

            ], 200);
        } catch (\Throwable $th) {
            $response = ['success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()];
            Log::error('LOG ERROR UPDATE OBJECTIVE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function addResourceValoration(Request $request)
    {
        DB::beginTransaction();

        try {
            $resource = Resource::create([
                'valuation_id' => $request['valuation'],
                'doctor_id' => $request['doctor_id'],
                'message_doctor' => $request['description'],
                'enable__touch_data' => $request['touchData']
            ]);
            foreach ($request['selectedQuestionnaires'] as $questionnaire) {
                $resource->questionnaires()->attach($questionnaire['id']);
            }

            foreach ($request['selectedResourceVideo'] as $resourceVideo) {
                $resource->resourceVideos()->attach($resourceVideo['resources_folder_content']['id']);
            }
            /*Notificamos al paciente de ha recibido un nuevo recurso*/
            $doctor = Doctor::where('id', $request['doctor_id'])->with('user')->first();
            $patient = Patient::where('user_id', $request['patient_id'])->with('user')->first();
            $valuation = Valuation::where('id', $request['valuation'])->with('treatment')->first();
            $subscription = Subscription::where('id', $valuation->subscription_id)->with('plan')->first();

            //Actualizamos el estado de la valoración
            $valuation->update(['state' => 2]);
            $valuation->save();
            //Enviamos la notificación al paciente
            $patient->user->notify(new ResourceSentNotification(
                $patient->user,
                $doctor->user,
                $valuation->name,
                $valuation->slug,
                $subscription->plan,
                $valuation->treatment->treatment
            ));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Add Resource',
                'response' => 'create_resource',
                'data' => $resource,

            ], 200);
        } catch (\Throwable $th) {
            $response = ['success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()];
            Log::error('LOG ERROR ADD RESOURCE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }

    }

    public function sendResourcePatient(Request $request)
    {
        DB::beginTransaction();
        $jsonBody = null;
        try {
            $resource = Resource::where('id', $request['id'])->with('doctor.user', 'valuation.treatment')->first();
            //Cambiamos el estado del recurso
            $resource->update(['state' => 2]);
            $resource->save();
            //Si permite los datos tactiles, los guarda
            if ($request['enable__touch_data'] == 1) {
                $jsonBody = json_encode($request['dataBody']);
                DB::table('resource_touch_data')
                    ->insert([
                        'resource_id' => $request['id'],
                        'data' => $jsonBody
                    ]);
            }
            //Guardamos las preguntas
            foreach ($request['questionnaires'] as $questionnaire) {
                foreach ($questionnaire['questions'] as $question) {
                    DB::table('answer_question_resource')
                        ->insert([
                            'resource_id' => $request['id'],
                            'questionnaire_id' => $questionnaire['id'],
                            'question_id' => $question['id'],
                            'value' => $question['type_question']['id'] == 4 ? json_encode($question['value']) : $question['value']
                        ]);
                }

            }

            $patientUser = User::where('id', auth()->id())->first();

            $resource->doctor->user->notify(new SendResourcePatient(
                $patientUser,
                $resource->doctor->user,
                $resource->valuation->name,
                $resource->valuation->slug,
                $resource->valuation->treatment
            ));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Send Resource Patient',
                'response' => 'send_resource_patient',
//                'data' => $resources,
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR SEND RESOURCE PATIENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function getResources($valuationId)
    {
        try {

            // Obtener los recursos y cargar los cuestionarios y preguntas relacionadas
            $resources = Resource::where('valuation_id', $valuationId)
                ->with('questionnaires.questions', 'questionnaires.questions.typeQuestion', 'questionnaires.treatments', 'resourceVideos.archive', 'touchDataHumanBody')
                ->get();
            foreach ($resources as $resource) {
                $resource->setAttribute('created_at_format', Date::parse($resource->created_at)->locale('es')->format('l d F Y'));
                $resource->setAttribute('dataBody', $resource->touchDataHumanBody ? json_decode($resource->touchDataHumanBody->data) : []);

                foreach ($resource->questionnaires as $questionnaire) {
                    foreach ($questionnaire->questions as $question) {
                        Log::info($resource->id);
                        Log::info($questionnaire->id);
                        $answer = AnswerQuestionResource::where('resource_id', $resource->id)->where('questionnaire_id', $questionnaire->id)->get();
                        Log::info($answer);
                        if (count($answer) > 0) {
                            $questionnaire->setAttribute('solved', true);
                            $questionnaire->setAttribute('resolsolved', true);
                        } else {
                            $questionnaire->setAttribute('solved', false);
                            $questionnaire->setAttribute('resolved', false);
                        }
                    }
                }

            }


            return response()->json([
                'success' => true,
                'message' => 'Get Resources',
                'response' => 'get_resources',
                'data' => $resources,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET RESOURCES.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getAnswerQuestionResource($data)
    {

        $jsonBody = json_decode($data);

        $dataBody = \App\Http\Resources\AnswerQuestionResource::collection(
            AnswerQuestionResource::where('resource_id', $jsonBody->resourceId)->where('questionnaire_id', $jsonBody->questionnaireId)->with('question.typeQuestion')->get()
        );
        if (count($dataBody) === 0) {
            $dataBody = QuestionsQuestionnaire::where('questionnaire_id', $jsonBody->questionnaireId)->with('typeQuestion')->get();
            foreach ($dataBody as $data) {
                $data->setAttribute('value', '');
            }
        }
////        $answers = AnswerQuestionResource::where('resource_id', $resourceId)->with('question')->get();
//
        return response()->json([
            'success' => true,
            'message' => 'Get Answers Resources',
            'response' => 'get_answers_resources',
            'data' => $dataBody,
        ], 200);
    }


}
