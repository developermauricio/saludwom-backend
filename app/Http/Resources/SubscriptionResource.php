<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'index' => $this->sequence_number,
            'subscription_plan_name' => $this->plan->name,
            'subscription_price' => $this->plan->price,
            'subscription_period' => $this->plan->period,
            'subscription_state' => $this->state,
            'subscription_order' => $this->order,
            'subscription_name' => $this->name,
            'subscription_created_at' => $this->created_at,
            'subscription_patient_name' => $this->patient->user->name .' '. $this->patient->user->last_name,
            'subscription_patient_user_id' => $this->patient->user->id
        ];
    }
}
