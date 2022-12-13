<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorScheduleResource extends JsonResource
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
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'date' => $this->date,
            'comment' => $this->comment,
            'state' => $this->state,
            'schedules_hours_minutes' => ScheduleHourMinuteDoctorResource::collection($this->schedulesHoursMinutes),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
