<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Resources\DoctorScheduleResource;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JamesMills\LaravelTimezone\Facades\Timezone;

class DoctorController extends Controller
{
    public function scheduleAvailable($id)
    {
        DB::beginTransaction();
        try {
            $scheduleAvailable = Doctor::where('id', $id)->with('user', 'doctorSchedule.schedulesHoursMinutes')->first();
            return response()->json([
                'success' => true,
                'message' => 'Check schedule available',
                'response' => 'check_schedule_available',
                'data' => $scheduleAvailable
            ], 200);

        }catch (\Throwable $th){
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHECK SCHEDULE AVAILABLE.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
//        DB::beginTransaction();
//        try {
//           $doctor = Doctor::where('id', $id)->with('user', 'doctorSchedule.schedulesHoursMinutes')->first();
//
//            $scheduleAvailable = DoctorScheduleResource::collection(
//                $doctor->doctorSchedule
//            );
//            return response()->json([
//                'success' => true,
//                'message' => 'Check schedule available',
//                'response' => 'check_schedule_available',
//                'data' => $scheduleAvailable
//            ], 200);
//
//        } catch (\Throwable $th) {
//            $response = [
//                'success' => false,
//                'message' => 'Transaction Error',
//                'error' => $th->getMessage(),
//                'trace' => $th->getTraceAsString()
//            ];
//            Log::error('LOG ERROR CHECK SCHEDULE AVAILABLE.', $response); // Guardamos el error en el archivo de logs
//            return response()->json($response, 500);
//        }
    }
}
