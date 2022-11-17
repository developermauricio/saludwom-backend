<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'biography', 'schedule'];

    public function treatments(){
        return $this->belongsToMany(TypeTreatment::class, 'doctor_type_treatment', 'doctor_id', 'type_treatment_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
