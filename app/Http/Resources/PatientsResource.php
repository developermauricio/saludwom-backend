<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

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

            'index' => $this->sequence_number,

            /* Total o cantidad para las valoraciones u objetivos*/
            'totalValorations' => $this->countTotalValuation,
            'totalPendSendRes' => $this->countTotalPendSenResources,
            'totalResSendFromDoctor' => $this->countTotalResoSedFromDoctor,
            'totalPendSendTreaFromDoctor' => $this->countTotalPendSendTreaFromDoctor,
            'totalInTreatment' => $this->countTotalInTreatment,
            'totalFinished' => $this->countTotalFinished,

            /* Total o cantidad para las subscripciones*/
            'totalSubscriptions' => $this->totalSubscriptions,
            'totalSubscriptionPending' => $this->totalSubscriptionPending,
            'totalSubscriptionCancelled' => $this->totalSubscriptionCancelled,
            'totalSubscriptionRejected' => $this->totalSubscriptionRejected,
            'totalSubscriptionAccepted' => $this->totalSubscriptionAccepted,
            'totalSubscriptionCompleted' => $this->totalSubscriptionCompleted,

            'patient_id' => $this->id,
            'user_id' => $this->user->id,
            'rowKey' => $this->id,
            'name' => $this->user->name.' '.$this->user->last_name,
            'lastName' => $this->user->last_name,
            'email' => $this->user->email,
            'user' => $this->user,
            'phone' => $this->user->phone,
            'state' => $this->user->state,
            'document' => $this->user->document,
            'birthday' => $this->user->birthday,
            'documentType' =>  $this->user->identificationType,
            'gender' => $this->gender,
            'address' => $this->user->address,
            'age' => Carbon::parse($this->user->birthday)->age,
            'slug' => $this->user->slug,
            'picture' => $this->user->picture,
            'signature' => $this->signature,
            'patientType' => $this->patient_type,
            'country' => $this->user->country ?? 'No registrado.',
            'country_flag' => $this->user->country ? $this->user->country->flag : '',
            'city' => $this->user->city ?? 'No regitrado.',
            'valoration' => $this->valuations,
            'subcrition' => $this->subcrition,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
