<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\RegisterDoctor;
use App\Http\Resources\DoctorAppointmentsResource;
use App\Http\Resources\DoctorScheduleResource;
use App\Http\Resources\PatientsResource;
use App\Http\Resources\ValorationResource;
use App\Mail\AccountCreateDoctor;
use App\Models\AppointmentValuation;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Gender;
use App\Models\SchedulesHoursMinute;
use App\Models\User;
use App\Models\Valuation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use JamesMills\LaravelTimezone\Facades\Timezone;
use function PHPUnit\Framework\once;

class DoctorController extends Controller
{
    public function addDoctor(RegisterDoctor $request)
    {
        DB::beginTransaction();
        $password = Str::random(10);
        $pass = Hash::make($password, ['rounds' => 15]);
        try {

            $user = User::create([
                'name' => ucwords($request['name']),
                'last_name' => ucwords($request['lastName']),
                'email' => $request['email'],
                'document' => $request['document'],
                'phone' => $request['phoneI'],
                'slug' => Str::slug($request['name'] . '-' . $request['lastName'] . '-' . Str::random(8), '-'),
                'password' => $pass,
                'birthday' => Carbon::parse($request['birthday'])->format('Y-m-d H:m:s'),
                'identification_type_id' => $request['documentType']['id'],
                'address' => $request['address'],
                'picture' => '/assets/images/user-profile.png',
                'city_id' => $request['city']['id']
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'biography' => $request['biography'],
                'zoom_api_key' => $request['zoomApiKey'],
                'zoom_api_secret' => $request['zoomApiSecret']
            ]);

            $user->roles()->attach(3);

            $newTreatmentsIds = array_map(function ($treatment) {
                return $treatment['id'];
            }, $request['treatments']);

            $this->addTreatments($newTreatmentsIds, $doctor);

            Mail::to($user->email)->send(new AccountCreateDoctor($user->name, $user->last_name, $password, $user->email));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Add Doctor',
                'response' => 'add_doctor',
                'data' => $doctor,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD DOCTOR.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }

    }

    public function editDoctor(RegisterDoctor $request, $doctorId)
    {
        DB::beginTransaction();

        try {

            $doctor = Doctor::find($doctorId);

            $doctor->update([
                'biography' => $request['biography'],
                'zoom_api_key' => $request['zoomApiKey'],
                'zoom_api_secret' => $request['zoomApiSecret']
            ]);

            $user = User::find($doctor->user_id);

            $user->update([
                'name' => ucwords($request['name']),
                'last_name' => ucwords($request['lastName']),
                'email' => $request['email'],
                'document' => $request['document'],
                'phone' => $request['phoneI'],
                'slug' => Str::slug($request['name'] . '-' . $request['lastName'] . '-' . Str::random(8), '-'),
                'birthday' => Carbon::parse($request['birthday'])->format('Y-m-d H:m:s'),
                'identification_type_id' => $request['documentType']['id'],
                'address' => $request['address'],
                'city_id' => $request['city']['id']
            ]);

            $currentTreatments = $doctor->treatments->pluck('id')->toArray();

            // Extrae los ID de las especialidades del request
            $newTreatmentsIds = array_map(function ($treatment) {
                return $treatment['id'];
            }, $request['treatments']);

            $treatmentsToRemove = array_diff($currentTreatments, $newTreatmentsIds);

            $treatmentsToAdd = array_diff($newTreatmentsIds, $currentTreatments);

            $this->addTreatments($treatmentsToAdd, $doctor);

            DB::table('doctor_type_treatment')
                ->where('doctor_id', $doctor->id)
                ->whereIn('type_treatment_id', $treatmentsToRemove)
                ->delete();

            DB::commit();

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR EDIT DOCTOR.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function addTreatments($treatments, $doctor)
    {
        foreach ($treatments as $treatment) {
            DB::table('doctor_type_treatment')
                ->updateOrInsert([
                    'type_treatment_id' => $treatment,
                    'doctor_id' => $doctor->id
                ]);
        }
    }

    public function scheduleAvailable($id)
    {
        DB::beginTransaction();
        try {
            $scheduleAvailable = Doctor::where('id', $id)->with('user', 'doctorSchedule.schedulesHoursMinutes')->first();
            return response()->json([
                'success' => true,
                'message' => 'Check schedule available',
                'response' => 'check_schedule_available',
                'data' => $scheduleAvailable
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHECK SCHEDULE AVAILABLE.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function scheduleDate($id, $date)
    {
        try {
            $parseDate = Carbon::parse($date);
            $scheduleDate = DoctorSchedule::where('doctor_id', $id)
                ->whereDate('date', $parseDate)->with('schedulesHoursMinutes.appointmentValuation.valuation')->first();

            return response()->json([
                'success' => true,
                'message' => 'Check schedule date',
                'response' => 'check_schedule_date',
                'data' => $scheduleDate
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHECK SCHEDULE DATE.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getValorations(Request $request, $dateFilter)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();
        $date = json_decode($dateFilter);

        try {
            if ($request->plan) {

                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])->where('doctor_id', $doctor->id)
                        ->whereHas('subscription', function ($q) use ($request) {
                            $q->where('plan_id', $request->plan);
                        })
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();

                } else {

                    $valorations = Valuation::where('doctor_id', $doctor->id)
                        ->whereHas('subscription', function ($q) use ($request) {
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
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])->where('doctor_id', $doctor->id)
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();

                } else {
                    $valorations = Valuation::where('doctor_id', $doctor->id)
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
            if ($request->state) {
//                $valorations = ValorationResource::collection(
//                    Valuation::where('doctor_id', $doctor->id)
//                        ->whereHas('patient', function ($q) use ($request) {
//                            $q->where('patient_type', );
//                        })->orderByDesc('created_at')->get());
                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])->where('doctor_id', $doctor->id)
                        ->where('state', $request->state)->with('patient')
                        ->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::where('doctor_id', $doctor->id)
                        ->where('state', $request->state)->with('patient')
                        ->orderByDesc('created_at')->get();
                }

                $valorations = $valorations->each(function ($valorations, $index) {
                    $valorations->sequence_number = $index + 1;
                });
                $valorations = ValorationResource::collection(
                    $valorations
                );
            }
            if ($request->genderId && $request->state && $request->plan) {
//                $valorations = ValorationResource::collection(
//                    Valuation::where('doctor_id', $doctor->id)
//                        ->whereHas('patient', function ($q) use ($request) {
//                            $q->where('gender_id', $request->genderId)->where('patient_type', $request->type);
//                        })->orderByDesc('created_at')->get());
                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])->where('doctor_id', $doctor->id)
                        ->where('state', $request->state)
                        ->whereHas('subscription', function ($q) use ($request) {
                            $q->where('plan_id', $request->plan);
                        })->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::where('doctor_id', $doctor->id)
                        ->where('state', $request->state)
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

            if ($request->genderId == null && $request->state == null && $request->plan == null) {

                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $valorations = Valuation::whereBetween('created_at', [$from, $to])->where('doctor_id', $doctor->id)
                        ->with(['patient.user.identificationType', 'patient.gender',
                            'patient.user.city', 'patient.user.country', 'subscription.plan'
                        ])->orderByDesc('created_at')->get();
                } else {
                    $valorations = Valuation::where('doctor_id', $doctor->id)
                        ->with(['patient.user.identificationType', 'patient.gender',
                            'patient.user.city', 'patient.user.country', 'subscription.plan'
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

    public function removeAvailabilityHourMinute($id, $doctorId, $dateSelected)
    {
        DB::beginTransaction();
        try {
            $deleteDoctorSchedule = false;
            $scheduleHourMinute = SchedulesHoursMinute::findOrFail($id);
            $scheduleHourMinute->delete();

            $doctorSchedule = DoctorSchedule::where('doctor_id', $doctorId)
                ->where('date', $dateSelected)->with('schedulesHoursMinutes')->first();

            if (count($doctorSchedule->schedulesHoursMinutes) <= 0) {
                $doctorSchedule->delete();
                $deleteDoctorSchedule = true;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'deleteDoctorSchedule' => $deleteDoctorSchedule,
                'message' => 'Now the schedule is no longer available',
                'response' => 'now_schedule'
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR REMOVER AVAILABILITY.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function addScheduleAvailable(Request $request)
    {
        DB::beginTransaction();

        $schedules = [];
        $doctorSchedule = new \stdClass();

        try {
            Log::info($request);

            if ($request['id']) {
                foreach ($request['hoursMinutes'] as $hoursMinute) {
                    $schedule = SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $request['id'],
                        'hour' => $hoursMinute['hour'],
                        'minute' => $hoursMinute['minute']
                    ]);

                    $schedules[] = $schedule;
                }
            } else {
                $doctorSchedule = DoctorSchedule::create([
                    'doctor_id' => $request['doctorId'],
                    'date' => $request['date']
                ]);

                foreach ($request['hoursMinutes'] as $hoursMinute) {
                    $schedule = SchedulesHoursMinute::create([
                        'doctor_schedule_id' => $doctorSchedule->id,
                        'hour' => $hoursMinute['hour'],
                        'minute' => $hoursMinute['minute']
                    ]);

                    $schedules[] = $schedule;
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'schedules' => $schedules,
                'doctorSchedule' => $doctorSchedule,
                'message' => 'Add schedule available',
                'response' => 'add_schedule_available'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD SCHEDULE AVAILABLE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function getAppointmentsDoctor($idDoctor)
    {
        $dateNow = Carbon::now();
        try {
            $appointments = DoctorSchedule::where('doctor_id', $idDoctor)
                ->where('date', '>=', $dateNow->format('Y-m-d'))
                ->where('state', 'AVAILABLE')
                ->with('schedulesHoursMinutes')->get();
            return response()->json([
                'success' => true,
                'message' => 'Get Appointments Doctor',
                'response' => 'get_appointments_doctor',
                'data' => $appointments
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET APPOINTMENTS DOCTOR.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function changeStatusDoctor($idDoctor)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($idDoctor);
            $state = $user->state == User::ACTIVE ? User::INACTIVE : User::ACTIVE;
            $user->state = $state;
            $user->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Change Status Doctor',
                'response' => 'change_status_doctor'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHANGE STATUS DOCTOR.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function getDoctorAppointmentsAgenda($idDoctor)
    {
        try {
            $appointments = DoctorAppointmentsResource::collection(
                AppointmentValuation::where('doctor_id', $idDoctor)
                    ->with('doctor', 'valuation.patient.user', 'schedulesHoursMinutes')->get()
            );
            return response()->json([
                'success' => true,
                'message' => 'Get Appointments Agenda Doctor',
                'response' => 'get_appointments_agenda_doctor',
                'data' => $appointments
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET APPOINTMENTS DOCTOR AGENDA.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function deleteDoctor($idDoctor)
    {
        DB::beginTransaction();
        $success = false;
        $message = 'The doctor was not removed';

        try {

            $valuations = Valuation::where('doctor_id', $idDoctor)->get();

            if (count($valuations) === 0) {

                $doctorTreatment = DB::table('doctor_type_treatment')
                    ->where('doctor_id', $idDoctor)
                    ->delete();

                $doctorSchedule = DoctorSchedule::where('doctor_id', $idDoctor)->get();
                if (count($doctorSchedule) > 0) {
                    foreach ($doctorSchedule as $value) {
                        SchedulesHoursMinute::where('doctor_schedule_id', $value->id)->delete();
                    }
                    DoctorSchedule::where('doctor_id', $idDoctor)->delete();
                }

                $doctor = Doctor::where('id', $idDoctor)->first();

                $user = User::where('id', $doctor->user_id)->first();

                $doctor->delete();

                $user->delete();

                $success = true;
                $message = 'Delete Doctor';
            }
            DB::commit();
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR DELETE DOCTOR.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
//    public function getCountPatients($idDoctorUser)
//    {
//        $doctor = Doctor::where('user_id', $idDoctorUser)->with('user')->first();
//        if (!$doctor) {
//            return response()->json([
//                'success' => true,
//                'message' => 'Doctor does not exist'
//            ], 404);
//        }
//        if (auth()->user()->hasRole('Doctor') || auth()->user()->hasRole('Admin')) {
//            $countTotalPatients = PatientsResource::collection(Valuation::where('doctor_id', $doctor->id)
//                ->with('patient.user.identificationType', 'patient.gender', 'patient.user.city.country')->get())->count();
//
//
//            $countStatePendingSendResources = Valuation::where('doctor_id', $doctor->id)->where('state', Valuation::PENDING_SEND_RESOURCES)->count();
//            $countStateResourcesSendFromDoctor = Valuation::where('doctor_id', $doctor->id)->where('state', Valuation::RESOURCES_SEND_FROM_DOCTOR)->count();
//            $countStateInTreatment = Valuation::where('doctor_id', $doctor->id)->where('state', Valuation::IN_TREATMENT)->count();
//            $countStateFinish = Valuation::where('doctor_id', $doctor->id)->where('state', Valuation::FINISHED)->count();
//
//
//            $totalPatients = new \stdClass();
//            $totalPatients->total = $countTotalPatients;
//            $totalPatients->type = 'totalPatients';
//
//            $totalStatePendingSendResources = new \stdClass;
//            $totalStatePendingSendResources->total = $countStatePendingSendResources;
//            $totalStatePendingSendResources->type = 'totalStateSendPendingResources';
//
//            $totalStateResourcesSendFromDoctor = new \stdClass();
//            $totalStateResourcesSendFromDoctor->total = $countStateResourcesSendFromDoctor;
//            $totalStateResourcesSendFromDoctor->type = 'totalStateResourcesSendFromDoctor';
//
//            $totalStateInTreatment = new \stdClass();
//            $totalStateInTreatment->total = $countStateInTreatment;
//            $totalStateInTreatment->type = 'totalStateInTreatment';
//
//            $totalStateFinish = new \stdClass();
//            $totalStateFinish->total = $countStateFinish;
//            $totalStateFinish->type = 'totalStateFinish';
//
//            return response()->json([
//                'success' => true,
//                'patients_for_doctor' => $doctor->user->name . ' ' . $doctor->user->last_name,
//                'data' => [
//                    $totalPatients,
//                    $totalStatePendingSendResources,
//                    $totalStateResourcesSendFromDoctor,
//                    $totalStateInTreatment,
//                    $totalStateFinish
//                ]
//            ]);
//        } else {
//            return response()->json([
//                'success' => true,
//                'message' => 'You have no access to this information'
//            ], 401);
//        }
//    }

//    public function getValorations()
//    {
//        $doctor = Doctor::where('user_id', auth()->user()->id)->first();
//        try {
//            $valuations = Valuation::where('doctor_id', $doctor->id)->with('doctor', 'patient.user', 'treatment', 'appointments.doctor.user')->latest('created_at')->paginate(12);
//            return response()->json([
//                'success' => true,
//                'message' => 'Get Valuations',
//                'response' => 'get_valuations',
//                'data' => $valuations,
//                'lastPage' => $valuations->lastPage(),
//                'total' => $valuations->total()
//            ], 200);
//        } catch (\Throwable $th) {
//            $response = [
//                'success' => false,
//                'message' => 'Transaction Error',
//                'error' => $th->getMessage(),
//                'trace' => $th->getTraceAsString()
//            ];
//            Log::error('LOG ERROR GET VALUATIONS.', $response); // Guardamos el error en el archivo de logs
//            return response()->json($response, 500);
//        }
//    }
}
