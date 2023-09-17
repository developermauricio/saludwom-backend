<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPatient;
use App\Http\Requests\UpdatePatientRequest;
use App\Mail\AccountActivation;
use App\Models\ActivationToken;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PatientController extends Controller
{
    public function checkSignature()
    {
        $patient = Patient::where('user_id', auth()->user()->id)->first();
        if ($patient->signature !== null && $patient->signature !== 'null' && $patient->signature !== '') {
            return response()->json($patient->signature);
        } else {
            return response()->json('no check signature');
        }
    }

    public function updateData(UpdatePatientRequest $request, $userId)
    {

        DB::beginTransaction();

        try {
            $user = User::where('id', $userId)->update([
                'name' => $request['name'],
                'last_name' => $request['lastName'],
                'email' => $request['email'],
                'phone' => $request['phoneI'],
                'birthday' => Carbon::parse($request['birthday'])->format('Y-m-d H:m:s'),
                'city_id' => $request['city'] ? $request['city']['id'] : null,
                'country_id' => $request['country']['id'],
            ]);

            Patient::where('user_id', $userId)
                ->update([
                    'gender_id' => $request['gender']['id']
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
