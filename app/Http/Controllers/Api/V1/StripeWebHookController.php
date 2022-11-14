<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Subscription;
use App\Notifications\ConfirmationSubscriptionNotification;
use App\Notifications\SendInvoiceNotification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $subscription = $patient->subcrition()->latest()->first();
        Log::info(json_encode($subscription->plan));
        DB::beginTransaction();
        try {
            if ($patient) {
                $order = $patient->orders()->latest()->first();
                $order->update([
                    'subscription_id' => $subscription['id'],
                    'invoice_id' => $invoice_id,
                    'state' => Order::ACCEPTED
                ]);

                $subscription ->update([
                    'state' => Subscription::ACCEPTED
                ]);

                $invoice = Invoice::create([
                    'patient_id' => $patient->id,
                    'plan_id' => $order->patient_id,
                    'order_id' => $order->id,
                    'invoice_stripe_id' => $invoice_id
                ]);

                $patient->user->notify(new SendInvoiceNotification($patient->user, $invoice, $order, $subscription->plan));
                $patient->user->notify(new ConfirmationSubscriptionNotification($patient->user, $subscription->plan,  $subscription));
                Log::info(json_encode($patient->user()));
                Log::info(json_encode($order));
                Log::info(json_encode($subscription));
                Log::info("Orden actualizado correctamente");
                DB::commit();
                return new Response('Webhook Handled: {handleChargeSucceeded}', 200);
            }
        } catch (\Exception $exception) {
//            $subscription->delete();
            Log::debug("Excepción Webhook {handleChargeSucceeded}: " . $exception->getMessage() . ", Line: " . $exception->getLine() . ', File: ' . $exception->getFile());
            DB::rollBack();
            return new Response('Webhook Handled with error: {handleChargeSucceeded}', 400);
        }
    }
}
