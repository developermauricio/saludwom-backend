<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Order;
use App\Models\Patient;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function intentStripe(Request $request)
    {
        $user = $request->user();
        if (!$user->hasDefaultPaymentMethod()) {
            $user->createOrGetStripeCustomer([
                'name' => $user->name . ' ' . $user->last_name,
                'phone' => $user->phone
            ]);
        }

        return $user->createSetupIntent();
    }

    public function createCard($data)
    {
        return PaymentMethod::create([
            'type' => 'card',
            'card' => [
                'number' => $data->card_number,
                'exp_month' => $data->card_exp_month,
                'exp_year' => $data->card_exp_year,
                'cvc' => $data->cvc,
            ]
        ]);
    }

    public function validatePeriod($period){
        $date = null;
        if ($period){
            switch ($period) {
                case 'week':
                    $date = Carbon::now()->addWeeks(1 );
                    break;
                case 'month':
                    $date = Carbon::now()->addMonth();
                    break;
                case 'year':
                    $date =  Carbon::now()->addYear();
                    break;
            }
            Log::info($date->format('Y-m-d H:i:s'));
            return $date->format('Y-m-d H:i:s');
        }
    }
    public function paymentStripe(Request $request)
    {

        DB::beginTransaction();
        try {
            $check = User::whereEmail($request->email)->first(); // Verificamos si existe el usuario
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $paymentMethod = $this->createCard($request);
            if (!$check) {
                $user = User::create([
                    'name' => $request->name,
                    'last_name' => $request->lastName,
                    'email' => $request->email,
                    'phone' => $request->phone
                ]);
            } else {
                $user = $request->user();
            }
            $patient = Patient::where('user_id', $user->id)->first();
            $amount = $request->amount * 100;
            $user->updateDefaultPaymentMethod($paymentMethod->id);
//            $payment = $user->charge($amount, $paymentMethod);
            $invoice = $user->invoiceFor($request->plan_name, $amount);
            $order = Order::create([
                'plan_id' => $request->plan,
                'patient_id' => $patient->id,
                'price_total' => $request->amount
            ]);
            Subscription::create([
                'plan_id' => $request->plan,
                'patient_id' => $patient->id,
                'expiration_date' => $this->validatePeriod($request->expiration_date_plan),
            ]);
            $user->save();
//            Log::info($payment->status);
//            if ($payment->status === 'succeeded') {
//
//                $order = Order::create([
//                    'plan_id' => $request->plan,
//                    'patient_id' => $patient->id,
//                    'price_total' => $request->amount
//                ]);
//                $user->save();
//            }
            DB::commit();
//            return response()->json(['status' => 'success', 'data' => ['payment' => $payment, 'Order' => $order]]);
            return response()->json(['status' => 'success', 'data' => ['Invoice' => $invoice, 'Order' => $order]]);


//            Log::info($paymentMethod->id);
//            $user->updateDefaultPaymentMethod($paymentMethod->id);
//
//            Log::info($user->hasPaymentMethod());
//
//            if (!$user->hasDefaultPaymentMethod()) {
//                Log::info('ENTRO EN EL IF');
//                $user->createAsStripeCustomer();
////                $user->createOrGetStripeCustomer([
////                    'name' => $user->name . ' ' . $user->last_name,
////                    'phone' => $user->phone
////                ]);
////                $user->createOrGetStripeCustomer();
//            }
//
//
//            $amount = $request->amount * 100;
////            $payment = $user->charge([
////                'amount' => $amount,
////                'payment_method' => $paymentMethod->id,
////            ]);
//            $payment = $user->charge($amount, $paymentMethod);
//
//            if ($payment->status === 'succeeded') {
//                $user->save();
//            }
//
//            return response()->json(['status' => 'success', 'data' => ['payment' => $payment]]);

        } catch (\Throwable $th) {
            /* Recibimos el error */
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR PAYMENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }
}
