<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'biography', 'schedule', 'zoom_api_key', 'zoom_api_secret'];

    public function treatments(){
        return $this->belongsToMany(TypeTreatment::class, 'doctor_type_treatment', 'doctor_id', 'type_treatment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctorSchedule(){
        $dateNow = Carbon::now();
        return $this->hasMany(DoctorSchedule::class, 'doctor_id')
            ->where('date', '>=', $dateNow->format('Y-m-d'))
            ->where('state', 'AVAILABLE');
    }

    public function valuations(){
        return $this->hasMany(Valuation::class);
    }
}
