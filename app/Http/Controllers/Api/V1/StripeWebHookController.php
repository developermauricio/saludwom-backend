<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Admin\NewSubscriptionConfirmation;
use App\Notifications\Patient\ConfirmationSubscriptionNotification;
use App\Notifications\SendInvoiceNotification;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;

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
        $patient = Patient::where('user_id', $user->id)->with('user.identificationType')->first();
        $subscription = $patient->subcrition()->latest()->first();
        $plan = $subscription->plan()->latest()->first();
        Log::info(json_encode($plan));
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
                    'state' => Subscription::ACCEPTED,
//                    'expiration_date' => Subscription::validatePeriod($plan->period)
                ]);

                $invoice = Invoice::create([
                    'patient_id' => $patient->id,
                    'plan_id' => $subscription['plan_id'],
                    'order_id' => $order->id,
                    'invoice_stripe_id' => $invoice_id
                ]);
                //Se notifica la factura
                $patient->user->notify(new SendInvoiceNotification($patient->user, $invoice, $order, $subscription->plan));
                //Se notifica la confimación de una nueva suscripción
                $patient->user->notify(new ConfirmationSubscriptionNotification($patient->user, $subscription->plan,  $subscription)); //Confirmación al paciente
                //Enviamos notificación a todos los usuarios que sean ADMIN
                $usersAdmin = User::role(['Admin', 'Asistente'])->get();
                foreach ($usersAdmin as $user){
                    $user->notify(new NewSubscriptionConfirmation($user, $patient->user, $subscription->plan,  $subscription));
                }

                Log::info(json_encode($order));
                Log::info(json_encode($subscription));
                Log::info("Orden actualizada correctamente");
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

    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($user) {
            $data = $payload['data']['object'];

            Log::info($data);

            // Buscar la suscripción existente
            $subscription = $user->subscriptions()->where('stripe_id', $data['id'])->first();

            Log::info($subscription);

            if ($subscription) {
                // Actualizar los valores de la suscripción existente
                $trialEndsAt = isset($data['trial_end']) ? Carbon::createFromTimestamp($data['trial_end']) : null;

                $subscription->update([
                    'stripe_status' => $data['status'],
                    'trial_ends_at' => $trialEndsAt,
                    'stripe_price' => count($data['items']['data']) === 1 ? $data['items']['data'][0]['price']['id'] : null,
                    'quantity' => count($data['items']['data']) === 1 && isset($data['items']['data'][0]['quantity']) ? $data['items']['data'][0]['quantity'] : null,
                ]);

                // Actualizar los elementos de la suscripción
                foreach ($data['items']['data'] as $item) {
                    $subscriptionItem = $subscription->items()->where('stripe_id', $item['id'])->first();

                    if ($subscriptionItem) {
                        $subscriptionItem->update([
                            'stripe_product' => $item['price']['product'],
                            'stripe_price' => $item['price']['id'],
                            'quantity' => $item['quantity'] ?? null,
                        ]);
                    } else {
                        // Crear el elemento si no existe (opcional)
                        $subscription->items()->create([
                            'stripe_id' => $item['id'],
                            'stripe_product' => $item['price']['product'],
                            'stripe_price' => $item['price']['id'],
                            'quantity' => $item['quantity'] ?? null,
                        ]);
                    }
                }

                // Obtener y actualizar fechas relevantes
                $periodStart = Carbon::createFromTimestamp($data['current_period_start']);
                $periodEnd = Carbon::createFromTimestamp($data['current_period_end']);
                $paidAt = isset($data['created']) ? Carbon::createFromTimestamp($data['created']) : null;

                // Actualizar la orden asociada
                $order = $subscription->order;
                if ($order) {
                    $order->update([
                        'paid_at' => $paidAt,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ]);
                }

                Log::info('La suscripción y la orden han sido actualizadas correctamente.');
            } else {
                Log::warning('No se encontró la suscripción en la base de datos.');
            }
        }

        return $this->successMethod();
    }

}
