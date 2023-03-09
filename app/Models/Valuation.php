<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valuation extends Model
{
    const PENDING_SEND_RESOURCES = 1;
    const RESOURCES_SEND_FROM_DOCTOR = 2;
    const PENDING_SEND_TREATMENT_FROM_DOCTOR = 3;
    const IN_TREATMENT = 4;
    const FINISHED = 5;

    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['name', 'patient_id', 'doctor_id', 'type_treatment_id', 'subscription_id', 'objectives', 'state', 'slug'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function archives()
    {
        return $this->morphMany(Archive::class, 'archiveable');
    }

    public function archive()
    {
        $this->archives()->firstOrCreate([
            'user_id' => auth()->id()
        ]);
    }
    public function appointments(){
        return $this->hasMany(AppointmentValuation::class, 'valuation_id')->orderByRaw("FIELD(state , '5', '1', '3', '2') ASC")->orderBy('date', 'ASC');
    }
    public function doctor(){
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient(){
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function treatment(){
        return $this->belongsTo(TypeTreatment::class, 'type_treatment_id');
    }

    public function subscription(){
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
