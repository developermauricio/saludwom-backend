<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\TypeTreatment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TreatmentController extends Controller
{
    public function getTreatments(){
        DB::beginTransaction();
        try {
            $treatments = TypeTreatment::with('categories', 'doctors.user', 'doctors.doctorSchedule')->get();
            return response()->json([
                'success' => true,
                'message' => 'Get treatments',
                'response' => 'get_treatments',
                'data' => $treatments
            ], 200);

        }catch (\Throwable $th){
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET TREATMENT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
