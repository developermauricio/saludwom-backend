<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionsController extends Controller
{
    public function getCurrentPatient()
    {
        return Patient::where('user_id', auth()->id())->first();
    }

    public function getSubscritions()
    {
        $patient = Patient::where('user_id', auth()->id())->first();
        try {
            $subscriptions = Subscription::where('patient_id', $patient->id)->with('plan')->latest('state')->paginate(8);
            return response()->json([
                'success' => true,
                'message' => 'Get Subscription',
                'response' => 'get_subscription',
                'data' => $subscriptions,
                'lastPage' => $subscriptions->lastPage(),
                'total' => $subscriptions->total()
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET SUBSCRIPTION.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function filterSubscriptions($filter): \Illuminate\Http\JsonResponse
    {
        $patient = $this->getCurrentPatient();
//        return response()->json();
//        $state1 = null;
//        $state2 = null;
        try {
            $subscriptions = Subscription::where('patient_id', $patient->id)->whereIn('state', json_decode($filter))->with('plan')->get();
            return response()->json([
                'success' => true,
                'message' => 'Get Filter Subscription',
                'response' => 'get_subscription',
                'filter' => $filter,
                'data' => $subscriptions,
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET SUBSCRIPTION.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function currentSubscrition()
    {
        $patient = Patient::where('user_id', auth()->user()->id)->first();
        $subscription = Subscription::where('patient_id', $patient->id)->where('state', '4')->with('plan')->first();
        return response()->json([
            'success' => true,
            'message' => 'Get Current Subscription',
            'response' => 'get_subscription',
            'data' => $subscription
        ], 200);
    }

    public function cancelSubscription($id)
    {

        DB::beginTransaction();
        if ($id) {

            $subscription = Subscription::find($id);

            if (!$subscription) {
                $response = [
                    'success' => true,
                    'message' => 'Suscripción no encontrada',
                    'data' => $subscription
                ];
                return response()->json($response, 201);
            }

            try {
                $subscription->state = 2;
                $subscription->save();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Suscripción cancelada exitosamente',
                    'response' => 'get_subscription',
                    'data' => $subscription
                ], 201);
            } catch (\Throwable $th) {
                $response = [
                    'success' => false,
                    'message' => 'Transaction Error',
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ];
                Log::error('LOG ERROR CANCELLED SUBSCRIPTION.', $response); // Guardamos el error en el archivo de logs
                DB::rollBack();
                return response()->json($response, 500);
            }

        }
    }
}
