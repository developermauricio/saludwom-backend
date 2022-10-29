<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\URL;

//use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request, User $user)
    {
        //Verificamos que la url es correcta
        if (!URL::hasValidSignature($request)) {
            return response()->json(['errors' => [
                'message' => 'Verificación del link es invalida'
            ]], 422);
        }
        //
        if ($user->hasVerifiedEmail()) {
            return response()->json(['errors' => [
                'message' => 'El correo electrónico ya ha sido verificado'
            ]], 422);
        }
        $user->markEmailAsVerified();
        event(new Verified($user));
        return response()->json(['message' => 'El correo electrónico ha sido verificado exitosamente'], 200);
    }

    public function resend(Request $request)
    {
        $this->validate($request,[
           'email' => ['email', 'required']
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user){
            return response()->json(['errors' => [
                'email' => 'No se ha podido encontrar ningún usuario con esta dirección de correo electrónico'
            ]], 422);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['errors' => [
                'message' => 'El correo electrónico ya ha sido verificado'
            ]], 422);
        }
        $user->sendEmailVerificationNotification();
        return response()->json(['status' => 'Enlace de verificación enviado de nuevo']);
    }
}
