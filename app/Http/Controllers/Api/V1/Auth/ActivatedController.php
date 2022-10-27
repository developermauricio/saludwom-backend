<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ActivationToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActivatedController extends Controller
{
    /*=============================================
       ACTIVAR CUENTA DEL USUARIO
   =============================================*/
    public function activateAccount(ActivationToken $token){
        $token->user->update([
            'state' => User::ACTIVE,
            'last_login' => Carbon::now(),
            'email_verified_at' => Carbon::now()
        ]);
        $token->delete(); //Eliminamos el token de activación de cuenta

        // Aqui autenticamos al usuario
        $tokenUser = JWTAuth::fromUser($token->user);
        return response()->json([
            'success'   => true,
            'message' => 'Account activated successfully',
            'response' => 'login_successful',
            'token' => $tokenUser
        ]);
    }
}
