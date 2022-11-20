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
    public function checkSignature(){
        $patient = Patient::where('user_id',auth()->user()->id)->first();
        if ($patient->signature !== null && $patient->signature !== 'null' && $patient->signature !== ''){
            return response()->json($patient->signature);
        }else{
            return response()->json('no check signature');
        }
    }
}
