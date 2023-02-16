<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Resources\DoctorScheduleResource;
use App\Http\Resources\PatientsResource;
use App\Models\Doctor;
use App\Models\Gender;
use App\Models\Valuation;
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

    public function getPatients(Request $request)
    {
//        dd($request);
        $doctor = Doctor::where('user_id', auth()->id())->first();
        try {
            if ($request->genderId) {

                $patients = PatientsResource::collection(
                    Valuation::where('doctor_id', $doctor->id)
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId);
                        })->get());
            }
            if ($request->type) {
                $patients = PatientsResource::collection(
                    Valuation::where('doctor_id', $doctor->id)
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('patient_type', $request->type);
                        })->get());
            }
            if ($request->genderId && $request->type) {
                $patients = PatientsResource::collection(
                    Valuation::where('doctor_id', $doctor->id)
                        ->whereHas('patient', function ($q) use ($request) {
                            $q->where('gender_id', $request->genderId)->where('patient_type', $request->type);
                        })->get());
            }

            if ($request->genderId == null && $request->type == null) {
                $patients = PatientsResource::collection(
                    Valuation::where('doctor_id', $doctor->id)
                        ->with(['patient.user.identificationType', 'patient.gender', 'patient.user.city', 'patient.user.country'])->get());
            }
            $countGenderM = $patients->filter(function ($item, $key){
               return $item->patient->gender_id === 1;
            });
            $countGenderF = $patients->filter(function ($item, $key){
                return $item->patient->gender_id === 2;
            });
            $countGenderO = $patients->filter(function ($item, $key){
                return $item->patient->gender_id === 3;
            });
            $countTypeClient = $patients->filter(function ($item, $key){
                return $item->patient->patient_type === 'client';
            });
            $countTypeCourtesy = $patients->filter(function ($item, $key){
                return $item->patient->patient_type === 'courtesy';
            });

            $totalPatients = new \stdClass();
            $totalPatients->total = $patients->count();
            $totalPatients->type = 'totalPatients';

            $totalPatientsM = new \stdClass();
            $totalPatientsM->total = $countGenderM->count();
            $totalPatientsM->type = 'totalPatientsM';

            $totalPatientsF = new \stdClass();
            $totalPatientsF->total = $countGenderF->count();
            $totalPatientsF->type = 'totalPatientsF';

            $totalPatientsO = new \stdClass();
            $totalPatientsO->total = $countGenderO->count();
            $totalPatientsO->type = 'totalPatientsO';

            $totalPatientsTypeCli = new \stdClass();
            $totalPatientsTypeCli->total = $countTypeClient->count();
            $totalPatientsTypeCli->type = 'totalPatientsCli';

            $totalPatientsTypeCour = new \stdClass();
            $totalPatientsTypeCour->total = $countTypeCourtesy->count();
            $totalPatientsTypeCour->type = 'totalPatientsCour';


            return response()->json([
                'success' => true,
                'total' => $patients->count(),
                'countData' => [
                    $totalPatients,
                    $totalPatientsM,
                    $totalPatientsF,
                    $totalPatientsO,
                    $totalPatientsTypeCli,
                    $totalPatientsTypeCour
                ],

                'message' => 'Get Patients',
                'response' => 'get_patients',
                'data' => $patients,
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

    public function getValorations()
    {
        $doctor = Doctor::where('user_id', auth()->user()->id)->first();
        try {
            $valuations = Valuation::where('doctor_id', $doctor->id)->with('doctor', 'patient.user', 'treatment', 'appointments.doctor.user')->latest('created_at')->paginate(12);
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
}
