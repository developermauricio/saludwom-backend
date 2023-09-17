<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Subscription;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    public function attemptLogin(Request $request)
    {
        $token = $this->guard()->attempt($this->credentials($request));

        if (!$token) {
            return false;
        }

        //Obtener el usuario autenticado
        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return false;
        }
        //Pasar token al usuario
        $this->guard()->setToken($token);
        return true;
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);
        $token = (string)$this->guard()->getToken();
        $expiration = $this->guard()->getPayload()->get('exp');

        $patient = Patient::where('user_id', auth()->user()->id)->first();
        $subscription = null;
        if ($patient) {
            $subscription = Subscription::where('patient_id', $patient->id)->where('state', '4')->with('plan')->first();
        }


        return response()->json([
            'token' => $token,
            'roles' => auth()->user()->getRoleNames(),
            'user' => auth()->user(),
            'subscription' => $subscription,
            'token_type' => 'bearer',
            'expiration_in' => $expiration
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json(['errors' => [
                'verification' => 'Necesitas verificar tu cuenta de correo electrónico'
            ]], 422);
        }
        throw ValidationException::withMessages([
            $this->username() => 'Credenciales de acceso incorrectas'
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        return response()->json(['message' => '¡Ha cerrado la sesión correctamente!']);
    }

    public function user(Request $request)
    {
        if (auth()->check()) {
            $user = User::where('id', auth()->user()->id)
                ->with('patient', function ($q) {
                    $q->with('currentSubscrition', function ($q) {
                        $q->where('state', '4')->with('plan')->first();
                    });
                })->with('patient.gender', 'city.country')->first();
            return response()->json(['user' => $user], 200);
        }
        return response()->json(null, 200);
    }

    public function updatePassword(Request $request, $userId)
    {

        DB::beginTransaction();

        try {
            $user = User::where('id', $userId)->update([
                'password' => Hash::make($request['password'])
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Update Password',
                'response' => 'update_password',
                'data' => $user,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPDATE PASSWORD.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function updatePhotoProfile(Request $request)
    {
        DB::beginTransaction();
        try {
            $photo = $request->file('photo');

            $photoProfile = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadPhotoLocal($photo) : $this->uploadPhotoStorage($photo);

            $user = User::where('id', auth()->id())
                ->update(['picture' => $photoProfile]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Update Password',
                'response' => 'update_password',
                'data' => $photoProfile,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPDATE PASSWORD.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function uploadPhotoLocal($photo)
    {
        $randomNamePhoto = 'photo-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
        Storage::disk('public')->put('/user/photo/' . $randomNamePhoto, file_get_contents($photo));
        return '/storage/user/photo/' . $randomNamePhoto;
    }

    public function uploadPhotoStorage($photo)
    {

        $randomNamePhoto = 'photo-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
        Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_USER_PHOTO') . '/' . $randomNamePhoto, file_get_contents($photo), 'public');
        return env('DIGITALOCEAN_FOLDER_USER_PHOTO') . '/' . $randomNamePhoto;
    }
}
