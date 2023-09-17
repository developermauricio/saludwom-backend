<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $index = 1;
        return [
            'id' => $this->id,
            'userId' => $this->user->id,
            'index' => $this->sequence_number,
            'countTotalValuation' => $this->countTotalValuation,
            'stateInTreatment' => $this->stateInTreatment,
            'statePendSendReso' => $this->statePendSendReso,
            'stateFinished' => $this->stateFinished,
            'fullName' => $this->user->name.' '.$this->user->last_name,
            'name' => $this->user->name,
            'lastName' => $this->user->last_name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'birthday' => $this->user->birthday,
            'address' => $this->user->address,
            'picture' => $this->user->picture,
            'document' => $this->user->document,
            'documentType' => $this->user->identificationType,
            'city' => $this->user->city,
            'country' => $this->user->city->country,
            'state' => $this->user->state,
            'biography' => $this->biography,
            'treatments' => $this->treatments,
            'valuations' => $this->valuations,
            'appointments' => $this->doctorSchedule,
            "zoomApiKey" => $this->zoom_api_key,
            "zoomApiSecret" => $this->zoom_api_secret
        ];
    }
}
