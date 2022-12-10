<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function getOrdersPatient(){
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
        }catch (\Throwable $th){
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

    public function downloadInvoice($orderId, $userId){

        try {
            $user = User::where('id',$userId)->with('patient')->first();
            $order = Order::where('id', $orderId)->with('subscription.plan', 'invoice')->first();
            $plan = $order->subscription->plan;
            $invoice = $order->invoice;
            $pdf = Pdf::loadView('mails.invoice', compact('user', 'invoice', 'order', 'plan'));
            return $pdf->download(config('app.name') . '-' . 'ORDEN DE COMPRA #'.$invoice->id.'-'.$user->name.' '.$user->last_name.'.pdf');
        }catch (\Throwable $th){
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
