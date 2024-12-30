<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\PatientsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPatient;
use App\Http\Requests\UpdatePatientRequest;
use App\Mail\AccountActivation;
use App\Mail\AccountCreatePatient;
use App\Models\ActivationToken;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Patient\ConfirmationSubscriptionNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class PatientController extends Controller
{

    public function exportData(Request $request)
    {
        $patientsSelected = $request->all();

        return Excel::download(new PatientsExport($patientsSelected), 'patients.xlsx', \Maatwebsite\Excel\Excel::XLSX, ['content-disposition' => 'Mauricio']);
    }

    public function checkSignature()
    {
        $patient = Patient::where('user_id', auth()->user()->id)->first();
        if ($patient->signature !== null && $patient->signature !== 'null' && $patient->signature !== '') {
            return response()->json($patient->signature);
        } else {
            return response()->json('no check signature');
        }
    }

    public function addPatient(RegisterPatient $request)
    {
        DB::beginTransaction();

        try {
            $password = Str::random(10);
            $user = User::create([

                'name' => ucwords($request['name']),
                'last_name' => ucwords($request['lastName']),
                'email' => $request['email'],
                'phone' => $request['phoneI'],
                'address' => $request['address'],
                'picture' => '/assets/images/user-profile.png',
                'password' => Hash::make($password),
                'document' => $request['document'],
                'identification_type_id' => $request['documentType'] ? $request['documentType']['id'] : null,
                'birthday' => Carbon::parse($request['birthday'])->format('Y-m-d H:m:s'),
                'city_id' => $request['city'] ? $request['city']['id'] : null,
                'country_id' => $request['country']['id'],
                'slug' => Str::slug(ucwords($request['name']) . '-' . ucwords($request['lastName']) . '-' . Str::random(8), '-')
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'gender_id' => $request['gender']['id'],
                'patient_type' => $request['patientType']['value'],
                'consent_forms' => 'ACCEPT'
            ]);

            $user->roles()->attach(2);

            Mail::to($user->email)->send(new AccountCreatePatient($user->name, $user->last_name, $password, $user->email));

            if ($request['plan']) {

                $subscription = Subscription::create([
                    'plan_id' => $request['plan']['id'],
                    'patient_id' => $patient->id,
                    'state' => Subscription::ACCEPTED,
                    'name' => 'Manual'
                ]);

                $order = Order::create([
                    'subscription_id' => $subscription->id,
                    'patient_id' => $patient->id,
                    'price_total' => 0,
                    'observations' => $request['observations'],
                    'state' => Order::ACCEPTED
                ]);

                Invoice::create([
                    'patient_id' => $patient->id,
                    'plan_id' => $request['plan']['id'],
                    'order_id' => $order->id
                ]);

                $user->notify(new ConfirmationSubscriptionNotification($user, $subscription->plan, $subscription));
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Add Patient',
                'response' => 'add_patient',
                'data' => $user,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD PATIENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function changeStatusPatient($userId)
    {
        DB::beginTransaction();

        try {

            $user = User::findOrFail($userId);
            $state = $user->state == User::ACTIVE ? User::INACTIVE : User::ACTIVE;
            $user->state = $state;
            $user->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Change Status Patient',
                'response' => 'change_status_patient'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHANGE STATUS PATIENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function updateData(UpdatePatientRequest $request, $userId)
    {

        DB::beginTransaction();

        try {

            $user = User::find($userId);

            $user->update([
                'name' => $request['name'],
                'last_name' => $request['lastName'],
                'email' => $request['email'],
                'phone' => $request['phoneI'],
                'document' => $request['document'] ? $request['document'] : $user->document,
                'identification_type_id' => $request['documentType'] ? $request['documentType']['id'] : $user->identification_type_id,
                'address' => $request['address'] ?? null,
                'birthday' => Carbon::parse($request['birthday'])->format('Y-m-d H:m:s'),
                'city_id' => $request['city'] ? $request['city']['id'] : $user->city_id,
                'country_id' => $request['country'] ? $request['country']['id'] : $user->country_id,
            ]);

            $patient = Patient::where('user_id', $userId)->first();

            $patient->update([
                'gender_id' => $request['gender']['id'],
                'patient_type' => $request['patientType'] ? $request['patientType']['value'] : $patient->patient_type
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Update Patient',
                'response' => 'update_patient',
                'data' => $user,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPDATE PATIENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }
}
