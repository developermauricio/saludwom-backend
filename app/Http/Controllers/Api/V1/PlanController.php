<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    public function getPlans(){
        DB::beginTransaction();
        try {
            $plans = Plan::all();
            return response()->json([
                'success' => true,
                'message' => 'Get Plans',
                'response' => 'get_plans',
                'data' => $plans
            ], 200);
        }catch (\Throwable $th){
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET PLANS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
