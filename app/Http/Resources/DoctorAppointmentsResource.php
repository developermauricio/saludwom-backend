<?php

namespace App\Http\Resources;

use App\Models\AppointmentValuation;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorAppointmentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $startDate = Carbon::parse($this->date);
        $endDate = $startDate->copy()->addMinutes(30);
        return [
            'title' => $this->valuation->patient->user->name . ' ' . $this->valuation->patient->user->last_name,
            'start' => $this->date,
            'end' => $endDate,
            'dateAppointment' => $this->date,
            'backgroundColor' => AppointmentValuation::stateColorAppointment($this->state),
            'borderColor' => AppointmentValuation::stateColorAppointment($this->state),
            'colorStatus' => AppointmentValuation::stateColorAppointment($this->state),
            'linkZoom' => $this->link_meeting_zoom,
            'state' => $this->state,
            'valuation' => $this->valuation
        ];
    }
}
