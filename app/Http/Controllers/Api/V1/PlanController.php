<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class PlanController extends Controller
{
    public function getPlans()
    {

        try {
            $plans = Plan::all();

            $plans = $plans->each(function ($plan, $index) {
                $plan->sequence_number = $index + 1;
                $plan->created_at_format = Date::parse($plan->created_at)->locale('es')->format('l d F Y H:i:s');
                $plan->update_at_format = Date::parse($plan->updated_at)->locale('es')->format('l d F Y H:i:s');
            });

            return response()->json([
                'success' => true,
                'message' => 'Get Plans',
                'response' => 'get_plans',
                'data' => $plans
            ], 200);
        } catch (\Throwable $th) {
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

    public function getPlansOrder()
    {
        try {

            $plans = Plan::orderBy('order')->get();

            return response()->json([
                'success' => true,
                'message' => 'Get Plans',
                'response' => 'get_plans',
                'data' => $plans
            ], 200);

        } catch (\Throwable $th) {
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

    public function updateStatus($planId)
    {

        DB::beginTransaction();

        try {
            $plan = Plan::findOrFail($planId);
            $state = $plan->state == Plan::ACTIVE ? Plan::INACTIVE : Plan::ACTIVE;
            $plan->state = $state;
            $plan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Change Status Plan',
                'response' => 'change_status_plan'
            ], 200);

        } catch (\Throwable $th) {

            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHANGE STATUS PLAN.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function updateOrderPlans(Request $request)
    {
        DB::beginTransaction();
        $plans = $request->all();
        $order = 1;

        try {
            foreach ($plans as $plan) {

                Plan::where('id', $plan['id'])->update(['order' => $order]);

                $order++;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Update Order Plan',
                'response' => 'update_order_plan',
            ], 200);
        }catch (\Throwable $th) {

            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHANGE ORDER PLANS.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function addPlan(Request $request)
    {
        DB::beginTransaction();
        $illustration = null;

        if ($request['imageBackground']) {
            $illustration = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadIllustrationLocal($request['imageBackground']) : $this->uploadIllustrationStorage($request['imageBackground']);
        }

        try {
            $maxOrder = Plan::max('order');
            $newOrder = $maxOrder + 1;

            $plan = Plan::create([
                'name' => ucwords($request['name']),
                'description' => $request['description'],
                'price' => $request['price'],
                'user_id' => auth()->id(),
                'period' => $request['period']['value'],
                'image_background' => $illustration,
                'order' => $newOrder,
                'number_appointments' => $request['numberAppointments'],
                'time_interval_appointments' => $request['timeIntervalAppointments']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Change Add Plan',
                'response' => 'change_add_plan'
            ], 200);

        } catch (\Throwable $th) {

            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD PLAN.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    /*=============================================
     AGREGAR ILUSTRACIÓN PREGUNTA
    =============================================*/
    public function uploadIllustrationLocal($illustration)
    {
        if (is_string($illustration) < 1) {
            $randomIllustration = 'illustration-' . Str::random(10) . '.' . $illustration['ext'];
            Storage::disk('public')->put('/illustration-plan/' . $randomIllustration, file_get_contents($illustration['urlResized']));
            return '/storage/illustration-plan/' . $randomIllustration;
        }
    }

    public function uploadIllustrationStorage($illustration)
    {
        if (is_string($illustration) < 1) {
            $randomIllustration = 'illustration-' . Str::random(10) . '.' . $illustration['ext'];
            Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_PLAN_BACKGROUND_IMAGE') . '/' . $randomIllustration, file_get_contents($illustration['urlResized']), 'public');
            return env('DIGITALOCEAN_FOLDER_PLAN_BACKGROUND_IMAGE') . '/' . $randomIllustration;
        }
    }


    public function editPlan(Request $request, $planId)
    {
        DB::beginTransaction();

        $illustration = null;

        if ($request['imageBackground']) {
            $illustration = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadIllustrationLocal($request['imageBackground']) : $this->uploadIllustrationStorage($request['imageBackground']);
        }

        try {

            $plan = Plan::find($planId);

            $plan->update([
                'name' => ucwords($request['name']),
                'description' => $request['description'],
                'price' => $request['price'],
                'user_id' => auth()->id(),
                'period' => $request['period']['value'],
                'image_background' => is_null($illustration) ? $plan->image_background : $illustration,
                'number_appointments' => $request['numberAppointments'],
                'time_interval_appointments' => $request['timeIntervalAppointments']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Edit Plan',
                'response' => 'edit_plan'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR EDIT PLAN.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function deletePlan($planId)
    {
        DB::beginTransaction();

        $success = false;
        $message = 'The plan was not removed';

        try {

            $patientSubscription = Subscription::where('plan_id', $planId)->get();

            if (count($patientSubscription) === 0) {

                $plan = Plan::find($planId);
                $plan->delete();

                $success = true;
                $message = 'Delete Plan';

            }

            DB::commit();
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR DELETE PLAN.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
