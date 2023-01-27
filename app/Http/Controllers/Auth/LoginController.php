<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Subscription;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
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
        if($patient){
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
            $user = User::where('id',auth()->user()->id)
                ->with('patient', function ($q)  {
                    $q->with('currentSubscrition', function ($q){
                        $q->where('state', '4')->with('plan')->first();
                    });
                })->first();
            return response()->json(['user' => $user], 200);
        }
        return response()->json(null, 200);
    }
}
