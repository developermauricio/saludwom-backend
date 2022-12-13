<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use JamesMills\LaravelTimezone\Facades\Timezone;

//use JamesMills\LaravelTimezone\Timezone;

class ScheduleHourMinuteDoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $date = Carbon::parse($this->doctorSchedule->date);
        $dateUserTimezone = Carbon::parse($date->format('Y-m-d').' '.$this->hour.':'.$this->minute.':00', 'UTC')->shiftTimezone('America/Bogota')->addHour();
        $formatDateUserTimeZone = Carbon::parse($dateUserTimezone);
        $hourTimezone = $formatDateUserTimeZone->format('Y-m-d H:i');
        return [
            'id' => $this->id,
            'doctor_schedule_id' => $this->doctor_schedule_id,
            'dateTimezone' => $dateUserTimezone,
            'hour' => $dateUserTimezone->setTimezone('UTC')->format('H'),
            'minute' => $dateUserTimezone->setTimezone('UTC')->format('i'),
            'dateOriginal' => $date->format('Y-m-d').' '.$this->hour.':'.$this->minute.':00',
            'hourOriginal' => $this->hour,
            'minuteOriginal' => $this->minute,
            'state' => $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
