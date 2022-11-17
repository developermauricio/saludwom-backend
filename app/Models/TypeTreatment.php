<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeTreatment extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const INACTIVE = 2;

    public function categories(){
       return $this->belongsToMany(CategoryTreatment::class, 'category_type_treatment', 'type_treatment_id', 'category_treatment_id');
    }
    public function doctors(){
        return $this->belongsToMany(Doctor::class, 'doctor_type_treatment', 'type_treatment_id', 'doctor_id');
    }
}
