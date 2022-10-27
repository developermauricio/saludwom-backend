<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPatient;
use App\Mail\AccountActivation;
use App\Models\ActivationToken;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PatientController extends Controller
{
    public function register(RegisterPatient $request){
        $role = Role::where('name', 'Patient')->first();
        DB::beginTransaction();
        try {
            $patient = User::create([
                'name' => ucwords($request->name),
                'last_name' => ucwords($request->lastName),
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'picture' => '/assets/images/user-profile.png',
                'slug' => Str::slug( ucwords($request->name). '-' .ucwords($request->lastName).'-'.Str::random(8), '-')
            ]);
            $patient->patient()->firstOrCreate([
                'user_id' => $patient->id,
                'patient_type' => 'client'
            ]);
            $patient->roles()->attach($role->id); // Asignamos el rol al usuario paciente

            $activationToken = ActivationToken::activationToken($patient);

            $response = [
                'success' => false,
                'message' => $activationToken
            ];
            Log::error('LOG TOKEN.', $response);
//            Mail::to($request->email)->send(new AccountActivation($activationToken->token,  $patient->name)); // Enviamos correo de activación de cuenta
//            /* Si la transacción fue correcta hacemos commit a la BD*/
//            DB::commit();
            return response()->json([
                'message' => 'Successfully registered patient',
                'response' => 'register_patient_user',
                'success' => true,
            ], 201);

        }catch (\Throwable $th) {
            /* Recibimos el error */

            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR REGISTER PATIENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack(); // Hacemos un rollback para eliminar cualquier registro almacenado en la BD
            return response()->json($response, 500);
        }
    }
}
