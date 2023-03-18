<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Resources\DoctorScheduleResource;
use App\Http\Resources\PatientsResource;
use App\Http\Resources\ValorationResource;
use App\Models\Doctor;
use App\Models\Gender;
use App\Models\Valuation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JamesMills\LaravelTimezone\Facades\Timezone;
use function PHPUnit\Framework\once;

class DoctorController extends Controller
{
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
