<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminAppointmentsResource;
use App\Http\Resources\DoctorsResource;
use App\Http\Resources\PatientsResource;
use App\Models\AppointmentValuation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Valuation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function getPatients(Request $request, $dateFilter)
    {
        $date = json_decode($dateFilter);

        try {
            // Inicia una consulta base
            $query = Patient::with(['user.identificationType', 'gender', 'user.city.country', 'valuations.treatment', 'valuations.doctor.user', 'subcrition.plan', 'subcrition.patient', 'subcrition.order']);

            if ($request->hasSubscription === '1') {
                $query->has('subcrition');
            }

            if ($request->hasSubscription === '0') {
                $query->doesntHave('subcrition');
            }

            if ($request->documentType) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('identification_type_id', $request->documentType);
                });
            }

            if ($request->gender) {
                $query->whereHas('gender', function ($q) use ($request) {
                    $q->where('gender_id', $request->gender);
                });
            }

            if ($request->type) {
                $query->where('patient_type', $request->type);
            }

            if ($request->state) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('state', $request->state);
                });
            }

            if (count((array)$date) > 0) {
                $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();

                $query->whereBetween('created_at', [$from, $to]);
            }

            $patients = $query->orderByDesc('created_at')->get();

            $patients = $patients->each(function ($patient, $index) {

                $patient->sequence_number = $index + 1;

                /*INFORAMCIÓN DE VALORACIONES O OBJETIVOS*/
                $patient->countTotalValuation = $patient->valuations->count();
                $patient->countTotalPendSenResources = $patient->valuations->where('state', '1')->count();
                $patient->countTotalResoSedFromDoctor = $patient->valuations->where('state', '2')->count();
                $patient->countTotalPendSendTreaFromDoctor = $patient->valuations->where('state', '3')->count();
                $patient->countTotalInTreatment = $patient->valuations->where('state', '4')->count();
                $patient->countTotalFinished = $patient->valuations->where('state', '5')->count();

                $patient->valuations->each(function ($valuations, $index) {
                    $valuations->sequence_number = $index + 1;
                });

                /*INFORAMCIÓN DE SUSCRIPCIONES*/
                $patient->totalSubscriptions = $patient->subcrition->count();
                $patient->totalSubscriptionPending = $patient->subcrition->where('state', '1')->count();
                $patient->totalSubscriptionCancelled = $patient->subcrition->where('state', '2')->count();
                $patient->totalSubscriptionRejected = $patient->subcrition->where('state', '3')->count();
                $patient->totalSubscriptionAccepted = $patient->subcrition->where('state', '4')->count();
                $patient->totalSubscriptionCompleted = $patient->subcrition->where('state', '5')->count();

                $patient->subcrition->each(function ($subcrition, $index) {
                    $subcrition->sequence_number = $index + 1;
                });
            });

            $patients = PatientsResource::collection($patients);

            //Total de pacientes
            $totalPatients = $query->count();


            // Llamar a la función para obtener el total de pacientes masculinos
            $queryOther = clone $query;
            $totalOtherPatients = $this->getTotalOtherPatients($queryOther);

            // Llamar a la función para obtener el total de pacientes masculinos
            $queryMale = clone $query;
            $totalMalePatients = $this->getTotalMalePatients($queryMale);

            // Llamar a la función para obtener el total de pacientes femeninos
            $totalFemalePatients = $this->getTotalFemalePatients($query);

            return response()->json([
                'success' => true,
                'message' => 'Get Patients',
                'response' => 'get_patients',
                'data' => $patients,
                'countData' => [
                    (object)[
                        'total' => $totalPatients,
                        'type' => 'totalPatients'
                    ],
                    (object)[
                        'total' => $totalMalePatients,
                        'type' => 'totalMale'
                    ],
                    (object)[
                        'total' => $totalFemalePatients,
                        'type' => 'totalFemale'
                    ],
                    (object)[
                        'total' => $totalOtherPatients,
                        'type' => 'totalOther'
                    ]
                ]
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

// Función para obtener el total de pacientes masculinos
    private function getTotalMalePatients($query)
    {
        return $query->whereHas('gender', function ($q) {
            $q->where('id', 1); // Asegúrate de que el ID coincida con el género masculino en tu base de datos
        })->count();
    }

// Función para obtener el total de pacientes femeninos
    private function getTotalFemalePatients($query)
    {
        return $query->whereHas('gender', function ($q) {
            $q->where('id', 2); // Asegúrate de que el ID coincida con el género femenino en tu base de datos
        })->count();
    }

    // Función para obtener el total de pacientes otro
    private function getTotalOtherPatients($query)
    {
        return $query->whereHas('gender', function ($q) {
            $q->where('id', 3); // Asegúrate de que el ID coincida con el género femenino en tu base de datos
        })->count();
    }


    public function getDoctors()
    {
        try {
            $doctors = Doctor::with('user', 'treatments')->get();

            return response()->json([
                'success' => true,
                'message' => 'Get Doctors',
                'response' => 'get_doctors',
                'data' => $doctors,
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET DOCTORS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getDoctorsAdmin()
    {
        try {
            $doctors = Doctor::with('user.identificationType', 'treatments', 'valuations.patient.user.city.country', 'valuations.treatment', 'doctorSchedule.schedulesHoursMinutes')->get();

            $doctors = $doctors->each(function ($doctors, $index) {
                $doctors->sequence_number = $index + 1;
                $doctors->countTotalValuation = $doctors->valuations->count();
                $doctors->countTotalPendSenResources = $doctors->valuations->where('state', '1')->count();
                $doctors->countTotalResoSedFromDoctor = $doctors->valuations->where('state', '2')->count();
                $doctors->countTotalPendSendTreaFromDoctor = $doctors->valuations->where('state', '3')->count();
                $doctors->countTotalInTreatment = $doctors->valuations->where('state', '4')->count();
                $doctors->countTotalFinished = $doctors->valuations->where('state', '5')->count();
                $doctors->valuations->each(function ($valuations, $index) {
                    $valuations->sequence_number = $index + 1;
                });
            });


            $doctors = DoctorsResource::collection(
                $doctors
            );

            return response()->json([
                'success' => true,
                'message' => 'Get Doctors',
                'response' => 'get_doctors',
                'data' => $doctors,
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET DOCTORS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getAppointmentsAgenda(Request $request)
    {
        try {

            $query = AppointmentValuation::with(['schedulesHoursMinutes', 'valuation.patient.user', 'doctor.user']);

            if ($request->has('selectedDoctors')) {
                $selectedDoctors = json_decode($request->selectedDoctors);

                // Asegúrate de que $selectedDoctors sea un array antes de usarlo en whereIn
                if (is_array($selectedDoctors)) {
                    $query->whereIn('doctor_id', $selectedDoctors);
                }
            }

            $appointments = $query->orderByDesc('created_at')->get();

            $appointments = $appointments->each(function ($appointment, $index) {
                $appointment->sequence_number = $index + 1;
            });

            $appointments = AdminAppointmentsResource::collection($appointments);

            return response()->json([
                'success' => true,
                'message' => 'Get Appointments',
                'response' => 'get_appointments',
                'data' => $appointments,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET APPOINTMENTS  AGENDA.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
