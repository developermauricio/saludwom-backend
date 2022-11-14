<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    const PENDING = 1;
    const CANCELLED = 2;
    const REJECTED = 3;
    const ACCEPTED = 4;
    const COMPLETED = 5;

    protected $guarded = ['id'];
    protected $fillable = ['plan_id', 'patient_id', 'expiration_date', 'state'];

    public function plan(){
        return $this->belongsTo(Plan::class);
    }
}
