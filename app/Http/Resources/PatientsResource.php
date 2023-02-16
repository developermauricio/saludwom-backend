<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientsResource extends JsonResource
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
            'id' => $this->patient->id,
            'user_id' => $this->patient->user->id,
            'rowKey' => $this->patient->id,
            'name' => $this->patient->user->name.' '.$this->patient->user->last_name,
            'email' => $this->patient->user->email,
            'phone' => $this->patient->user->phone,
            'document' => $this->patient->user->document,
            'document_type' => $this->patient->user->identificationType->name,
            'gender' => $this->patient->gender->name,
            'age' => Carbon::parse($this->patient->user->birthday)->age,
            'slug' => $this->patient->user->slug,
            'picture' => $this->patient->user->picture,
            'patient_type' => $this->patient->patient_type,
            'country' => $this->patient->user->country ? $this->patient->user->country->name : 'No registrado.',
            'country_flag' => $this->patient->user->country ? $this->patient->user->country->flag : '',
            'city' => $this->patient->user->city ? $this->patient->user->city->name : 'No regitrado.',
            'created_at' => $this->patient->created_at,
            'updated_at' => $this->patient->updated_at,
        ];
    }
}
