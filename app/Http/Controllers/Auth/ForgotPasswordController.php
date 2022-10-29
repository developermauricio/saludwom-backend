<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{

    use SendsPasswordResetEmails;
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json(['status' => 'Enlace de restablecimiento enviado con éxito.'], 200);
    }
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(['email' => 'No podemos encontrar un usuario con esa dirección de correo electrónico.'], 422);
    }
}
