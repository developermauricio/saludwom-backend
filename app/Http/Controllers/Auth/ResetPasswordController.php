<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;
    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json(['status' => 'Tu cuenta ha sido restablecida correctamente.'], 200);
    }


    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json(['email' => 'El token de restablecimiento de contraseña no es válido. Solicita un nuevo link para restablecer tu contraseña.'], 422);
    }
}
