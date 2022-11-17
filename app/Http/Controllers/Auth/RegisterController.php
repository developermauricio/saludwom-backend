<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    protected function registered(Request $request, User $user){
        return response()->json($user, 200);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {

        $role = Role::where('name', 'Patient')->first();

        $user = User::create([
            'name' => ucwords($data['name']),
            'last_name' => ucwords($data['lastName']),
            'email' => $data['email'],
            'phone' => $data['phoneI'],
            'city_id' => $data['city'] ? $data['city']['id'] : null,
            'country_id' => $data['country']['id'],
            'picture' => '/assets/images/user-profile.png',
            'password' => Hash::make($data['password']),
            'slug' => Str::slug( ucwords($data['name']). '-' .ucwords($data['lastName']).'-'.Str::random(8), '-')
        ]);
        $user->patient()->firstOrCreate([
            'user_id' => $user->id,
            'gender_id' =>  $data['gender']['id'],
            'patient_type' => 'client'
        ]);
        $user->roles()->attach($role->id); // Asignamos el rol al usuario paciente

        return $user;
    }
}
