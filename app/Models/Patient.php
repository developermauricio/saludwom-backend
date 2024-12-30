<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gender_id',
        'signature',
        'consent_forms',
        'patient_type'
    ];

    const COURTESY = 'courtesy';
    const CLIENT = 'client';

    public function orders(){
        return $this->hasMany(Order::class);
    }
    public function subcrition(){
        return $this->hasMany(Subscription::class, 'patient_id', 'id');
    }

    public function couponUser(){
        return $this->hasMany(CouponUser::class);
    }

    public function valuations(){
        return $this->hasMany(Valuation::class);
    }

    public function currentSubscrition(){
        return $this->hasOne(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

}
