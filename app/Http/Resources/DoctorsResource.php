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
            'index' => $this->sequence_number,
            'name' => $this->user->name.' '.$this->user->last_name,
            'state' => $this->user->state,
            'biography' => $this->biography,
            'treatments' => $this->treatments
        ];
    }
}
