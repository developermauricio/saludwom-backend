<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientsResource;
use App\Models\Valuation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function getPatients(){
        try {
            $patients = PatientsResource::collection(Valuation::with('patient.user.identificationType', 'patient.gender', 'patient.user.city.country')->get());
            return response()->json([
                'success' => true,
                'message' => 'Get Patients',
                'response' => 'get_patients',
                'data' => $patients,
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET PATIENTS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}