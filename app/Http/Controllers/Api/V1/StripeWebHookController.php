<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Patient;
use App\Models\Subscription;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeWebHookController extends WebhookController
{
    /**
     *
     * WEBHOOK que se encarga de obtener un evento al hacer un pago correctamente
     * charge.refunded
     *
     * @param array $payload
     * @return Response|\Symfony\Component\HttpFoundation\Response
     */
    public function handleChargeSucceeded($payload)
    {
        $invoice_id = $payload['data']['object']["invoice"];
        Log::info(json_encode($payload));
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        $patient = Patient::where('user_id', $user->id)->first();
        $subcription = $patient->subcrition()->latest()->first();
        try {
            if ($patient) {
                $order = $patient->orders()->latest()->first();
                $order->update([
                    'invoice_id' => $invoice_id,
                    'state' => Order::ACCEPTED
                ]);

                $subcription->update([
                    'state' => Subscription::ACCEPTED
                ]);
               
                Log::info(json_encode($user));
                Log::info(json_encode($order));
                Log::info(json_encode($subcription));
                Log::info("Orden actualizado correctamente");
                return new Response('Webhook Handled: {handleChargeSucceeded}', 200);
            }
        } catch (\Exception $exception) {
            $subcription->delete();
            Log::debug("Excepción Webhook {handleChargeSucceeded}: " . $exception->getMessage() . ", Line: " . $exception->getLine() . ', File: ' . $exception->getFile());
            return new Response('Webhook Handled with error: {handleChargeSucceeded}', 400);
        }
    }
}
