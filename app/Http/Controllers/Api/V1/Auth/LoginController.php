<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;


class LoginController extends Controller
{

    /*=============================================
      LOGIN
  =============================================*/
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) { // validamos la creación del token
                return response()->json([
                    'success'   => false,
                    'message'   => 'Invalid credentials',
                    'response' => 'invalid_credentials',
                    'data'      => []
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success'   => false,
                'message'   => 'Could not create token',
                'response' => 'could_not_create_token',
                'data'      => $e->getMessage()
            ], 500);
        }

        $user = User::find(auth()->user()->id); //Buscarmos el usuario
        if ($user->status == 2) {
            return response()->json([
                'success'   => false,
                'message'   => 'User is currently banned',
                'response' => 'user_banned'
            ], 401);
        }

        if ($user->email_verified_at == null) {
            return response()->json([
                'success'   => false,
                'message'   => 'The account has not yet been activated, please check your email address to perform the account validation process',
                'response' => 'activate_account',
                'data' => $user
            ], 401);
        }

        $user->last_login = Carbon::now();
        $user->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Login successful',
            'response' => 'login_successful',
            'token' => $token
        ], 200);
    }

    public function user()
    {
        $user = User::where('id', auth()->id())->first();
        return response()->json(['user' => $user]);
    }

    public function logout(Request $request){
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
