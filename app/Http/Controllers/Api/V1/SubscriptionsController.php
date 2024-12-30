<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Patient;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionsController extends Controller
{
    public function getCurrentPatient()
    {
        return Patient::where('user_id', auth()->id())->first();
    }

    public function getSubscritionsAdmin(Request $request, $dateFilter)
    {

        Log::info($dateFilter);

        $date = json_decode($dateFilter);
        $subscriptions = [];

        try {

            if ($request->plan) {

                if (count((array)$date) > 0) {

                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();

                    $subscriptions = Subscription::whereBetween('created_at', [$from, $to])
                        ->where('plan_id', $request->plan)
                        ->with(['plan', 'patient', 'order'])
                        ->orderByDesc('created_at')->get();

                } else {

                    $subscriptions = Subscription::where('plan_id', $request->plan)
                        ->with(['plan', 'patient', 'order'])
                        ->orderByDesc('created_at')->get();
                }

            }

            if ($request->state) {

                if (count((array)$date) > 0) {

                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();

                    $subscriptions = Subscription::whereBetween('created_at', [$from, $to])
                        ->where('state', $request->state)
                        ->with(['plan', 'patient', 'order'])
                        ->orderByDesc('created_at')->get();
                } else {

                    $subscriptions = Subscription::where('state', $request->state)
                        ->with(['plan', 'patient'])->orderByDesc('created_at')->get();
                }

            }

            if (is_null($request->state) && is_null($request->plan)) {

                if (count((array)$date) > 0) {
                    $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                    $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();
                    $subscriptions = Subscription::whereBetween('created_at', [$from, $to])
                        ->with(['plan', 'patient', 'order'])->orderByDesc('created_at')->get();
                } else {
                    $subscriptions = Subscription::with(['plan', 'patient'])->orderByDesc('created_at')->get();
                }
            }

            $subscriptions = $subscriptions->each(function ($subscriptions, $index) {
                $subscriptions->sequence_number = $index + 1;
            });

            $subscriptions = SubscriptionResource::collection(
                $subscriptions
            );

            $countStatePending = $subscriptions->filter(function ($item, $key) {
                return $item->state === '1';
            });
            $countStateCancelled = $subscriptions->filter(function ($item, $key) {
                return $item->state === '2';
            });
            $countStateRejected = $subscriptions->filter(function ($item, $key) {
                return $item->state === '3';
            });
            $countStateAccepted = $subscriptions->filter(function ($item, $key) {
                return $item->state === '4';
            });
            $countStateCompleted = $subscriptions->filter(function ($item, $key) {
                return $item->state === '5';
            });

            $totalSubscriptions = new \stdClass();
            $totalSubscriptions->total = $subscriptions->count();
            $totalSubscriptions->type = 'totalSubscriptions';

            $totalPending = new \stdClass();
            $totalPending->total = $countStatePending->count();
            $totalPending->type = 'totalPending';

            $totalCancelled = new \stdClass();
            $totalCancelled->total = $countStateCancelled->count();
            $totalCancelled->type = 'totalCancelled';

            $totalRejected = new \stdClass();
            $totalRejected->total = $countStateRejected->count();
            $totalRejected->type = 'totalRejected';

            $totalAccepted = new \stdClass();
            $totalAccepted->total = $countStateAccepted->count();
            $totalAccepted->type = 'totalAccepted';

            $totalCompleted = new \stdClass();
            $totalCompleted->total = $countStateCompleted->count();
            $totalCompleted->type = 'totalCompleted';

            return response()->json([
                'success' => true,
                'total' => $subscriptions->count(),
                'countDataState' => [
                    $totalSubscriptions,
                    $totalPending,
                    $totalCancelled,
                    $totalRejected,
                    $totalAccepted,
                    $totalCompleted
                ],

                'message' => 'Get Valorations',
                'response' => 'get_valorations',
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

    public function getSubscritions()
    {
        $patient = Patient::where('user_id', auth()->id())->first();
        try {
            $subscriptions = Subscription::where('patient_id', $patient->id)->with('plan')->latest('state')->paginate(10);
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
