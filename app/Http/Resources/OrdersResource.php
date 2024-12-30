<?php

namespace App\Http\Resources;

use Akaunting\Money\Money;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'index' => $this->sequence_number,

            'orden_id' => $this->id,
            'orden_subscription_name' => $this->subscription->name,
            'orden_plan_name' => $this->subscription->plan->name,
            'orden_plan_description' => $this->subscription->plan->description,
            'orden_state' => $this->state,
            'orden_price' => Money::EUR($this->price_total),
            'orden_discount' => $this->discount ? Money::EUR($this->discount) : null,
            'orden_patient_user_id' => $this->patient->user_id,
            'orden_patient_user_name' => $this->patient->user->name.' '.$this->patient->user->last_name,
            'orden_patient_document' => $this->patient->user->document,
            'orden_patient_email' => $this->patient->user->email,
            'orden_patient_phone' => $this->patient->user->phone,
            'orden_patient_user_type_identification' => $this->patient->user->identificationType,
            'orden_created_at' => $this->created_at,
        ];
    }
}
