<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrdersResource;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function getOrdersPatient()
    {
        $patient = Patient::where('user_id', auth()->id())->first();
        try {
            $orders = Order::where('patient_id', $patient->id)
                ->orderBy('created_at', 'DESC')
                ->with('subscription.plan')->get();
            return response()->json([
                'success' => true,
                'message' => 'Get Orders Patient',
                'response' => 'get_sorders_patient',
                'data' => $orders,

            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET ORDERS PATIENT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getOrdersAdmin(Request $request, $dateFilter)
    {
        $date = json_decode($dateFilter);

        try {

            $query = Order::with(['subscription.plan', 'patient.user.identificationType']);

            if ($request->plan) {
                $query->whereHas('subscription', function ($q) use ($request) {
                    $q->where('plan_id', $request->plan);
                });
            }

            if ($request->state) {
                $query->where('state', $request->state);
            }

            if (count((array)$date) > 0) {
                $from = Carbon::createFromFormat('Y-m-d', $date->start)->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', $date->end)->endOfDay();

                $query->whereBetween('created_at', [$from, $to]);
            }

            $orders = $query->orderByDesc('created_at')->get();

            $orders = $orders->each(function ($order, $index) {
                $order->sequence_number = $index + 1;

                $order->countTotalPlan = $order->subscription->count();

            });

            $orders = OrdersResource::collection($orders);

            $queryStates = clone $query;

            $totalStates = $this->getTotalStates($queryStates);

            return response()->json([
                'success' => true,
                'message' => 'Get Orders',
                'response' => 'get_orders',
                'data' => $orders,
                'countDataState' => $totalStates
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET ORDERS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getTotalStates($query): array
    {

        $queryPending = clone $query;
        $queryCancelled = clone $query;
        $queryRejected = clone $query;
        $queryAccepted = clone $query;
        $all = clone $query;

        $totalOrders = $all->count();
        $totalPending = $queryPending->where('state', '1')->count();
        $totalCancelled = $queryCancelled->where('state', '2')->count();
        $totalRejected = $queryRejected->where('state', '3')->count();
        $totalAccepted = $queryAccepted->where('state', '4')->count();

        return [
            (object)['total' => $totalOrders, 'type' => 'totalOrders'],
            (object)['total' => $totalPending, 'type' => 'totalPending'],
            (object)['total' => $totalCancelled, 'type' => 'totalCancelled'],
            (object)['total' => $totalRejected, 'type' => 'totalRejected'],
            (object)['total' => $totalAccepted, 'type' => 'totalAccepted'],
        ];
    }

    public function downloadInvoice($orderId, $userId)
    {
        try {
            $user = User::where('id', $userId)->with('patient')->first();
            $order = Order::where('id', $orderId)->with('subscription.plan', 'invoice')->first();
            $plan = $order->subscription->plan;
            $subscription = $order->subscription;
            $invoice = $order->invoice;
            $pdf = Pdf::loadView('mails.invoice', compact('user', 'invoice', 'order', 'plan', 'subscription'));

            return $pdf->download(config('app.name') . '-' . 'ORDEN DE COMPRA #' . $invoice->id . '-' . $user->name . ' ' . $user->last_name . '.pdf');
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET ORDERS PATIENT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
