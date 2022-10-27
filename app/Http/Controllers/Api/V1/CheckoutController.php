<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    public function intent(Request $request)
    {
        $response = [
            'success' => false,
            'message' => 'Transaction Error',
            'error' => $request->user(),

        ];
        Log::error('LOG ERROR USUARIO.', $response);
        $user = $request->user();

        return $user->createSetupIntent();
    }

    public function pay(Request $request)
    {
        $user = $request->user();
        $paymentMethod = $request->payment_method;
        $storeCard = $request->storeCard;
        $cardholderName = $request->name;
        $address = $request->billing_address;
        $amount = $request->amount;

        try {
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentMethod);
            $amount = $amount * 100; //convert to cent/kobo,etc
            $payment = $user->charge($amount, $paymentMethod);

            if ($payment->status === 'succeeded') {
                $user->billing_address = $address;
                $user->save();
            }

            return response()->json(['status' => 'success', 'data' => ['payment' => $payment]]);
        } catch (\Throwable $th) {
            /* Recibimos el error */
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CREATE MONITOR.', $response); // Guardamos el error en el archivo de logs

            return response()->json($response, 500);
        }
    }

    public function payment(Request $request){
        $check = User::whereEmail($request->email)->first(); // Verificamos si existe el usuario

        if(!$check){
            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone
            ]);
        }
    }
}
