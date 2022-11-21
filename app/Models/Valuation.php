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
    protected $fillable = ['name', 'patient_id', 'doctor_id', 'type_treatment_id', 'subscription_id', 'objectives', 'state'];

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

}
