<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Coupon;
use App\Models\CouponUser;
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



    public function paymentStripe(Request $request)
    {
        Log::info($request);
        $couponExists = Coupon::where('id', $request->coupon)->first();
        DB::beginTransaction();
        try {

            Stripe::setApiKey(env('STRIPE_SECRET'));
            $paymentMethod = $this->createCard($request);

            Log::info($request->documentDocumentType);
            if ($request->documentNumber !== 'null' && $request->documentDocumentType !== 'null') {

                $typeDocument = json_decode($request->documentDocumentType);

                $dataUser = User::find(auth()->user()->id);
                $dataUser->document = $request->documentNumber;
                $dataUser->identification_type_id = $typeDocument->id;
                $dataUser->save();

            }
            $user = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();
            $amount = $request->amount * 100;
            $user->updateDefaultPaymentMethod($paymentMethod->id);
            $invoice = $user->invoiceFor($request->plan_name, $amount);
            $order = Order::create([
                'patient_id' => $patient->id,
                'price_total' => $request->total * 100,
                'discount' => $request->discount === 'null' ? null : $request->discount * 100,
                'coupon_id' => $request->coupon === 'null' ? null : $request->coupon
            ]);
            Subscription::create([
                'plan_id' => $request->plan,
                'patient_id' => $patient->id,
            ]);
            /* Si existe el cupón*/
            if ($couponExists) {
                CouponUser::create([
                    'patient_id' => $patient->id,
                    'coupon_id' => $couponExists->id,
                    'order_id' => $order->id
                ]);
            }

            $user->save();
            DB::commit();
            return response()->json(['status' => 'success', 'data' => ['Invoice' => $invoice, 'Order' => $order]]);

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
